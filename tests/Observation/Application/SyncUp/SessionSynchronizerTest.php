<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\SyncUp;

use App\Census\Domain\Exception\CensusNotFoundException;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Application\SyncUp\SessionSynchronizer;
use App\Observation\Application\SyncUp\SyncSkippedException;
use App\Observation\Application\SyncUp\SyncValidationException;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\SessionType;
use PHPUnit\Framework\TestCase;

class SessionSynchronizerTest extends TestCase
{
    private SessionSynchronizer $synchronizer;

    protected function setUp(): void
    {
        $this->synchronizer = new SessionSynchronizer();
    }

    public function test_it_creates_a_new_session_with_observations_and_participants(): void
    {
        $dto = SessionSyncDto::fromArray($this->getValidPayload());

        $validCodesSet = ['#OR-N-0001' => true, '#OR-N-0002' => true];
        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $session = $this->synchronizer->sync($dto, '550e8400-e29b-41d4-a716-446655440045', null, $validCodesSet, $validBirdsSet, []);

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $session->id()->value());
        $this->assertEquals(SessionType::Station, $session->sessionType());
        $this->assertEquals(41.0, $session->latitude()->value());
        $this->assertFalse($session->isDeleted());

        $this->assertCount(2, $session->observations());

        $obsList = $session->observations()->toArray();
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440004', $obsList[0]->id()->value());
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440002', $obsList[0]->speciesId()->value());
        $this->assertEquals(ContactType::Visual, $obsList[0]->contactType());
        $this->assertEquals(3, $obsList[0]->quantity()->value());
        $this->assertFalse($obsList[0]->isDeleted());

        $this->assertCount(2, $session->participants(), 'Hauria de tenir només els participants existents a validCodesSet');

        $partsList = $session->participants()->toArray();
        $this->assertEquals('#OR-N-0001', $partsList[0]->ornithologistCode()->value());
        $this->assertEquals('#OR-N-0002', $partsList[1]->ornithologistCode()->value());
    }

    public function test_it_updates_an_existing_session_and_adds_new_observations(): void
    {
        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $dtoOld = SessionSyncDto::fromArray($this->getValidPayload('2023-01-01T00:00:00Z'));
        $existingSession = $this->synchronizer->sync($dtoOld, '110e8400-e29b-41d4-a716-446655440002', null, ['#OR-N-0001' => true], $validBirdsSet, []);

        $this->assertEquals(41.0, $existingSession->latitude()->value());
        $this->assertCount(2, $existingSession->observations());

        // El mòbil envia les 2 observacions anteriors com a deleted + 1 nova
        $newPayload = $this->getValidPayload('2023-02-01T10:00:00Z');
        $newPayload['latitude'] = 42.5;
        $newPayload['observations'] = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440004', 'speciesId' => '550e8400-e29b-41d4-a716-446655440002', 'contactType' => 0, 'quantity' => 3,
                'startTime' => '2023-10-25T10:10:00Z', 'endTime' => '2023-10-25T10:15:00Z',
                'createdAt' => '2023-01-01T00:00:00Z', 'updatedAt' => '2023-02-01T10:00:00Z', 'isDeleted' => true,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440030', 'speciesId' => '550e8400-e29b-41d4-a716-446655440006', 'contactType' => 1, 'quantity' => 1,
                'startTime' => '2023-10-25T11:00:00Z', 'endTime' => '2023-10-25T11:05:00Z',
                'createdAt' => '2023-01-01T00:00:00Z', 'updatedAt' => '2023-02-01T10:00:00Z', 'isDeleted' => true,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440099', 'speciesId' => '550e8400-e29b-41d4-a716-446655440002', 'contactType' => 1, 'quantity' => 10,
                'startTime' => '2023-10-25T10:00:00Z', 'endTime' => '2023-10-25T11:00:00Z',
                'createdAt' => '2023-02-01T10:00:00Z', 'updatedAt' => '2023-02-01T10:00:00Z', 'isDeleted' => false,
            ],
        ];
        $newPayload['participants'] = [];

        $dtoNew = SessionSyncDto::fromArray($newPayload);
        $updatedSession = $this->synchronizer->sync($dtoNew, '110e8400-e29b-41d4-a716-446655440002', $existingSession, [], $validBirdsSet, []);

        $this->assertSame($existingSession, $updatedSession);
        $this->assertEquals(42.5, $updatedSession->latitude()->value());

        // 3 observacions: les 2 originals ara marcades com a deleted + 1 nova
        $obsList = $updatedSession->observations()->toArray();
        $this->assertCount(3, $obsList);

        $deletedObs = array_filter($obsList, fn($o) => $o->isDeleted());
        $this->assertCount(2, $deletedObs, 'Les 2 observacions antigues haurien d\'estar marcades com a deleted');

        $activeObs = array_filter($obsList, fn($o) => !$o->isDeleted());
        $this->assertCount(1, $activeObs, 'Hauria d\'haver 1 observació activa nova');

        $this->assertCount(0, $updatedSession->participants());
    }

    public function test_it_throws_sync_skipped_exception_if_incoming_data_is_older(): void
    {
        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $dtoOld = SessionSyncDto::fromArray($this->getValidPayload('2023-10-25T15:00:00Z'));
        $existingSession = $this->synchronizer->sync($dtoOld, '110e8400-e29b-41d4-a716-446655440002', null, [], $validBirdsSet, []);

        $dtoNew = SessionSyncDto::fromArray($this->getValidPayload('2023-10-25T10:00:00Z'));

        $this->expectException(SyncSkippedException::class);

        $this->synchronizer->sync($dtoNew, '110e8400-e29b-41d4-a716-446655440002', $existingSession, [], $validBirdsSet, []);
    }

    public function test_it_throws_exception_if_census_is_required_but_not_found(): void
    {
        $payload = $this->getValidPayload();
        $payload['sessionType'] = SessionType::Census->value;
        $payload['censusId'] = '220e8400-e29b-41d4-a716-446655440002';
        $dto = SessionSyncDto::fromArray($payload);

        $this->expectException(CensusNotFoundException::class);

        // $validCensusSet buit → cens no existeix
        $this->synchronizer->sync($dto, '110e8400-e29b-41d4-a716-446655440002', null, [], [], []);
    }

    public function test_it_allows_census_if_found_in_valid_census_set(): void
    {
        $payload = $this->getValidPayload();
        $payload['sessionType'] = SessionType::Census->value;
        $payload['censusId'] = '220e8400-e29b-41d4-a716-446655440002';
        $payload['listeningPoint'] = 1;
        $payload['latitude'] = 41.0;
        $payload['longitude'] = 2.0;
        $payload['locationSource'] = 0;
        $dto = SessionSyncDto::fromArray($payload);

        $validCensusSet = ['220e8400-e29b-41d4-a716-446655440002' => true];
        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $session = $this->synchronizer->sync($dto, '110e8400-e29b-41d4-a716-446655440002', null, [], $validBirdsSet, $validCensusSet);

        $this->assertEquals('220e8400-e29b-41d4-a716-446655440002', $session->censusId()->value());
    }

    public function test_session_updated_at_bumped_by_observation_with_newer_timestamp(): void
    {
        $sessionTimestamp = '2023-10-25T10:00:00Z';
        $obsTimestamp = '2023-10-25T11:30:00Z'; // Més nova que la sessió

        $payload = $this->getValidPayload($sessionTimestamp);
        $payload['observations'][0]['updatedAt'] = $obsTimestamp;

        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $dto = SessionSyncDto::fromArray($payload);
        $session = $this->synchronizer->sync($dto, '550e8400-e29b-41d4-a716-446655440045', null, [], $validBirdsSet, []);

        $this->assertEquals(new \DateTimeImmutable($obsTimestamp), $session->updatedAt());
    }

    public function test_it_rejects_session_with_future_created_at(): void
    {
        $payload = $this->getValidPayload();
        $payload['createdAt'] = '2099-01-01T00:00:00Z';

        $this->expectException(SyncValidationException::class);
        $this->expectExceptionMessage('future_timestamps');

        $this->synchronizer->sync(SessionSyncDto::fromArray($payload), '110e8400-e29b-41d4-a716-446655440002', null, [], [], []);
    }

    public function test_it_rejects_session_with_future_updated_at(): void
    {
        $payload = $this->getValidPayload('2099-01-01T00:00:00Z');

        $this->expectException(SyncValidationException::class);
        $this->expectExceptionMessage('future_timestamps');

        $this->synchronizer->sync(SessionSyncDto::fromArray($payload), '110e8400-e29b-41d4-a716-446655440002', null, [], [], []);
    }

    public function test_it_rejects_observation_with_future_timestamps(): void
    {
        $payload = $this->getValidPayload();
        $payload['observations'][0]['createdAt'] = '2099-01-01T00:00:00Z';
        $payload['observations'][0]['updatedAt'] = '2099-01-01T00:00:00Z';

        $validBirdsSet = [
            '550e8400-e29b-41d4-a716-446655440002' => true,
            '550e8400-e29b-41d4-a716-446655440006' => true,
        ];

        $this->expectException(SyncValidationException::class);
        $this->expectExceptionMessage('future_timestamps');

        $this->synchronizer->sync(SessionSyncDto::fromArray($payload), '110e8400-e29b-41d4-a716-446655440002', null, [], $validBirdsSet, []);
    }

    // --- HELPER ---

    private function getValidPayload(string $updatedAt = '2023-10-25T12:00:00Z'): array
    {
        return [
            'id' => '550e8400-e29b-41d4-a716-446655440001',
            'censusId' => null,
            'sessionType' => SessionType::Station->value,
            'startTime' => '2023-10-25T10:00:00Z',
            'endTime' => '2023-10-25T12:00:00Z',
            'temperature' => 1, 'wind' => 0, 'sky' => 0, 'rain' => 0, 'visibility' => 2, 'moon' => 0,
            'listeningPoint' => 1, 'latitude' => 41.0, 'longitude' => 2.0,
            'accuracy' => 10.0, 'altitude' => 100.0, 'altitudeAccuracy' => null,
            'locationSource' => 0,
            'createdAt' => '2023-01-01T00:00:00Z',
            'updatedAt' => $updatedAt,
            'observations' => [
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440004', 'speciesId' => '550e8400-e29b-41d4-a716-446655440002', 'contactType' => 0, 'quantity' => 3,
                    'startTime' => '2023-10-25T10:10:00Z', 'endTime' => '2023-10-25T10:15:00Z',
                    'notes' => null, 'latitude' => null, 'longitude' => null, 'accuracy' => null,
                    'altitude' => null, 'altitudeAccuracy' => null, 'locationSource' => null, 'bearing' => 0.0,
                    'createdAt' => '2023-01-01T00:00:00Z', 'updatedAt' => $updatedAt, 'isDeleted' => false,
                ],
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440030', 'speciesId' => '550e8400-e29b-41d4-a716-446655440006', 'contactType' => 1, 'quantity' => 1,
                    'startTime' => '2023-10-25T11:00:00Z', 'endTime' => '2023-10-25T11:05:00Z',
                    'notes' => 'cantant', 'latitude' => null, 'longitude' => null, 'accuracy' => null,
                    'altitude' => null, 'altitudeAccuracy' => null, 'locationSource' => null, 'bearing' => 90.0,
                    'createdAt' => '2023-01-01T00:00:00Z', 'updatedAt' => $updatedAt, 'isDeleted' => false,
                ]
            ],
            'participants' => [
                ['ornithologistCode' => '#OR-N-0001'],
                ['ornithologistCode' => '#OR-N-0002'],
                ['ornithologistCode' => '#OR-N-0003'], // NO EXISTEIX → s'ha de filtrar
            ],
        ];
    }
}
