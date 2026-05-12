<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\SyncUp;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Application\SyncUp\SessionSynchronizer;
use App\Observation\Application\SyncUp\SyncSkippedException;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\SessionType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionSynchronizerTest extends TestCase
{
    private CensusRepositoryInterface&MockObject $censusRepository;
    private SessionSynchronizer $synchronizer;

    protected function setUp(): void
    {
        $this->censusRepository = $this->createMock(CensusRepositoryInterface::class);
        $this->synchronizer = new SessionSynchronizer($this->censusRepository);
    }

    public function test_it_creates_a_new_session_with_observations_and_participants(): void
    {
        // 1. Preparem el DTO complet amb Observacions i Participants
        $dto = SessionSyncDto::fromArray($this->getValidPayload());

        $validCodesSet = ['#OR-N-0001' => true, '#OR-N-0002' => true]; // ORN-3 no hi és, l'hauria d'ignorar

        // 2. Executem la sincronització (com que existingSession és null, farà CREATE)
        $session = $this->synchronizer->sync($dto, '550e8400-e29b-41d4-a716-446655440045', null, $validCodesSet);

        // 3. Asserts de la Sessió Base
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $session->id()->value());
        $this->assertEquals(SessionType::Station, $session->sessionType());
        $this->assertEquals(41.0, $session->latitude()->value());

        // 4. Asserts de les Observacions
        $this->assertCount(2, $session->observations());

        $obsList = $session->observations()->toArray();
        // Mirem si la primera observació s'ha mapejat bé
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440004', $obsList[0]->id()->value());
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440002', $obsList[0]->speciesId()->value());
        $this->assertEquals(ContactType::Visual, $obsList[0]->contactType());
        $this->assertEquals(3, $obsList[0]->quantity()->value());

        // 5. Asserts dels Participants (Comprovem que ha filtrat els invàlids)
        $this->assertCount(2, $session->participants(), 'Hauria de tenir només els participants existents a validCodesSet');

        $partsList = $session->participants()->toArray();
        $this->assertEquals('#OR-N-0001', $partsList[0]->ornithologistCode()->value());
        $this->assertEquals('#OR-N-0002', $partsList[1]->ornithologistCode()->value());
    }

    public function test_it_updates_an_existing_session_and_replaces_collections(): void
    {
        // 1. Instanciem una sessió "Antiga" directament (simulant la de la BBDD)
        $dtoOld = SessionSyncDto::fromArray($this->getValidPayload('2023-01-01T00:00:00Z'));
        // Posem un valor de test a la latitud de la vella perquè vegem el canvi
        $existingSession = $this->synchronizer->sync($dtoOld, '110e8400-e29b-41d4-a716-446655440002', null, ['#OR-N-0001' => true]);

        // Comprovem que l'antiga tenia una certa data i latitud (que ve del payload per defecte: 41.0)
        $this->assertEquals(41.0, $existingSession->latitude()->value());
        $this->assertCount(2, $existingSession->observations());

        // 2. Preparem el DTO "Nou" (que ve del mòbil) per actualitzar la sessió
        $newPayload = $this->getValidPayload('2023-02-01T10:00:00Z');
        $newPayload['latitude'] = 42.5; // Canviem la latitud
        $newPayload['observations'] = [
            // El mòbil ha esborrat les 2 observacions d'abans i n'envia 1 de nova
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001', 'speciesId' => '550e8400-e29b-41d4-a716-446655440002', 'contactType' => 1, 'quantity' => 10,
                'startTime' => '2023-10-25T10:00:00Z', 'endTime' => '2023-10-25T11:00:00Z',
                'createdAt' => '2023-02-01T10:00:00Z', 'updatedAt' => '2023-02-01T10:00:00Z'
            ]
        ];
        $newPayload['participants'] = []; // El mòbil envia que ja no hi ha participants

        $dtoNew = SessionSyncDto::fromArray($newPayload);

        // 3. Executem la sincronització en mode UPDATE
        $updatedSession = $this->synchronizer->sync($dtoNew, '110e8400-e29b-41d4-a716-446655440002', $existingSession, []);

        // 4. Asserts: Comprovem que SÓN EL MATEIX OBJECTE en memòria i s'han actualitzat els camps
        $this->assertSame($existingSession, $updatedSession, 'Ha de ser la mateixa instància (Update), no crear-ne una de nova');
        $this->assertEquals(42.5, $updatedSession->latitude()->value(), 'S\'hauria d\'haver actualitzat la latitud');

        // MÀGIA DEL SNAPSHOT: Comprovem que ha fet clearObservations() i n'ha posat només 1
        $this->assertCount(1, $updatedSession->observations());
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $updatedSession->observations()->first()->id()->value());

        // Comprovem que ha esborrat els participants
        $this->assertCount(0, $updatedSession->participants());
    }

    public function test_it_throws_sync_skipped_exception_if_incoming_data_is_older(): void
    {
        // Data a la base de dades (Més Nova)
        $dtoOld = SessionSyncDto::fromArray($this->getValidPayload('2023-10-25T15:00:00Z'));
        $existingSession = $this->synchronizer->sync($dtoOld, '110e8400-e29b-41d4-a716-446655440002', null, []);

        // Data del mòbil (Més Vella)
        $dtoNew = SessionSyncDto::fromArray($this->getValidPayload('2023-10-25T10:00:00Z'));

        $this->expectException(SyncSkippedException::class);

        // Ha de petar just en entrar i no tocar res
        $this->synchronizer->sync($dtoNew, '110e8400-e29b-41d4-a716-446655440002', $existingSession, []);
    }

    public function test_it_throws_exception_if_census_is_required_but_not_found(): void
    {
        $payload = $this->getValidPayload();
        $payload['sessionType'] = SessionType::Census->value;
        $payload['censusId'] = '220e8400-e29b-41d4-a716-446655440002';
        $dto = SessionSyncDto::fromArray($payload);

        // Simulem que el repositori diu que aquest cens NO existeix a la BBDD
        $this->censusRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(CensusNotFoundException::class);

        $this->synchronizer->sync($dto, '110e8400-e29b-41d4-a716-446655440002', null, []);
    }

    public function test_it_allows_census_if_found_in_repository(): void
    {
        $payload = $this->getValidPayload();
        $payload['sessionType'] = SessionType::Census->value;
        $payload['censusId'] = '220e8400-e29b-41d4-a716-446655440002';
        $dto = SessionSyncDto::fromArray($payload);

        $mockCensus = $this->createMock(Census::class);

        // 2. Retornem el mock en lloc del stdClass
        $this->censusRepository->expects($this->once())
            ->method('findById')
            ->willReturn($mockCensus);

        $session = $this->synchronizer->sync($dto, '110e8400-e29b-41d4-a716-446655440002', null, []);

        $this->assertEquals('220e8400-e29b-41d4-a716-446655440002', $session->censusId()->value());
    }

    // --- HELPER D'ESTRUCTURA DE DADES ---

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
            'createdAt' => '2023-10-25T10:00:00Z',
            'updatedAt' => $updatedAt,
            'observations' => [
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440004', 'speciesId' => '550e8400-e29b-41d4-a716-446655440002', 'contactType' => 0, 'quantity' => 3,
                    'startTime' => '2023-10-25T10:10:00Z', 'endTime' => '2023-10-25T10:15:00Z',
                    'notes' => null, 'latitude' => null, 'longitude' => null, 'accuracy' => null,
                    'altitude' => null, 'altitudeAccuracy' => null, 'locationSource' => null, 'bearing' => 0.0,
                    'createdAt' => '2023-10-25T10:10:00Z', 'updatedAt' => $updatedAt
                ],
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440030', 'speciesId' => '550e8400-e29b-41d4-a716-446655440006', 'contactType' => 1, 'quantity' => 1,
                    'startTime' => '2023-10-25T11:00:00Z', 'endTime' => '2023-10-25T11:05:00Z',
                    'notes' => 'cantant', 'latitude' => null, 'longitude' => null, 'accuracy' => null,
                    'altitude' => null, 'altitudeAccuracy' => null, 'locationSource' => null, 'bearing' => 90.0,
                    'createdAt' => '2023-10-25T11:00:00Z', 'updatedAt' => $updatedAt
                ]
            ],
            'participants' => [
                ['ornithologistCode' => '#OR-N-0001'], // Existeix
                ['ornithologistCode' => '#OR-N-0002'], // Existeix
                ['ornithologistCode' => '#OR-N-0003'], // NO EXISTEIX (S'ha de filtrar i ignorar)
            ]
        ];
    }
}
