<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\SyncUp;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Application\SyncUp\SessionSynchronizer;
use App\Observation\Application\SyncUp\SyncSkippedException;
use App\Observation\Application\SyncUp\SyncUpHandler;
use App\Observation\Application\SyncUp\SyncUpRequest;
use App\Observation\Application\SyncUp\SyncValidationException;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\SessionId;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SyncUpHandlerTest extends TestCase
{
    private SessionRepositoryInterface&MockObject $sessionRepository;
    private ProfileRepositoryInterface&MockObject $profileRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private BirdRepositoryInterface&MockObject $birdRepository;
    private CensusRepositoryInterface&MockObject $censusRepository;
    private SessionSynchronizer&MockObject $synchronizer;
    private SyncUpHandler $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->profileRepository = $this->createMock(ProfileRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->birdRepository = $this->createMock(BirdRepositoryInterface::class);
        $this->censusRepository = $this->createMock(CensusRepositoryInterface::class);
        $this->synchronizer = $this->createMock(SessionSynchronizer::class);

        $this->handler = new SyncUpHandler(
            $this->sessionRepository,
            $this->profileRepository,
            $this->transactionManager,
            $this->birdRepository,
            $this->censusRepository,
            $this->synchronizer,
        );
    }

    public function test_it_returns_empty_response_when_no_sessions_provided(): void
    {
        $request = new SyncUpRequest('annotator-123', []);

        // No s'hauria de fer cap crida a repositoris ni transaccions
        $this->sessionRepository->expects($this->never())->method('findByIds');
        $this->transactionManager->expects($this->never())->method('begin');

        $response = $this->handler->handle($request);

        $this->assertEmpty($response->synced);
        $this->assertEmpty($response->failed);
    }

    public function test_it_fails_session_immediately_if_id_is_missing_or_invalid(): void
    {
        $validSessionId = '550e8400-e29b-41d4-a716-446655440001';
        $validSessionData = $this->getValidSessionData($validSessionId);

        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [
            ['no-id' => 'here'],        // Falta ID
            ['id' => 12345],            // ID no és string (és un int)
            $validSessionData           // Dades vàlides completes
        ]);

        $this->sessionRepository->method('findByIds')->willReturn([]);
        $this->sessionRepository->method('findByCensusIdsAndAnnotator')->willReturn([]);
        $this->profileRepository->method('findValidOrnithologistCodes')->willReturn([]);
        $this->birdRepository->method('findValidBirdIds')->willReturn([]);
        $this->censusRepository->method('findCensusIdsByStatus')->willReturn([]);

        // 2. Només s'hauria d'iniciar la transacció 1 vegada (per a la sessió vàlida)
        $this->transactionManager->expects($this->once())->method('begin');

        $mockSession = $this->createMock(Session::class);
        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->willReturn($mockSession);

        // Act executem el handler
        $response = $this->handler->handle($request);

        // Assert de la sessió sincronitzada
        $this->assertContains($validSessionId, $response->synced);
        $this->assertCount(1, $response->synced);

        $this->assertCount(2, $response->failed);

        // La primera falla perquè no té 'id'
        $this->assertEquals('unknown', $response->failed[0]['sessionId']);
        $this->assertEquals('invalid_data', $response->failed[0]['reason']);

        // La segona falla perquè l''id' és un enter, no un string
        $this->assertEquals('unknown', $response->failed[1]['sessionId']);
        $this->assertEquals('invalid_data', $response->failed[1]['reason']);
    }

    public function test_it_successfully_syncs_a_new_session_and_frees_memory(): void
    {
        $sessionData = $this->getValidSessionData('session-1');
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [$sessionData]);

        $this->sessionRepository->expects($this->once())
            ->method('findByIds')
            ->with(['session-1'])
            ->willReturn([]); // No hi ha sessió prèvia

        $this->profileRepository->expects($this->once())
            ->method('findValidOrnithologistCodes')
            ->with(['ORN-1'])
            ->willReturn(['ORN-1']);

        $mockSession = $this->createMock(Session::class);

        // El synchronizer hauria de rebre "null" a $existingSession
        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->with($this->isInstanceOf(SessionSyncDto::class), '550e8400-e29b-41d4-a716-446655440001', null, ['ORN-1' => true], $this->anything(), $this->anything())
            ->willReturn($mockSession);

        $this->transactionManager->expects($this->once())->method('begin');
        $this->sessionRepository->expects($this->once())->method('save')->with($mockSession);
        $this->transactionManager->expects($this->once())->method('commit');

        // Assegurem que s'allibera la memòria a l'acabar
        $this->sessionRepository->expects($this->once())->method('free')->with($mockSession);

        $response = $this->handler->handle($request);

        $this->assertContains('session-1', $response->synced);
        $this->assertEmpty($response->failed);
    }

    public function test_it_successfully_syncs_an_existing_session(): void
    {
        $sessionData = $this->getValidSessionData('session-2');
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [$sessionData]);

        $mockExistingSession = $this->createMock(Session::class);
        $mockSessionId = $this->createMock(SessionId::class);
        $mockSessionId->method('value')->willReturn('session-2');
        $mockExistingSession->method('id')->willReturn($mockSessionId);

        $this->sessionRepository->method('findByIds')->willReturn([$mockExistingSession]);

        // Verifiquem que se li passa el mock existent al synchronizer
        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->with($this->anything(), '550e8400-e29b-41d4-a716-446655440001', $mockExistingSession, $this->anything(), $this->anything(), $this->anything())
            ->willReturn($mockExistingSession);

        $this->transactionManager->expects($this->once())->method('commit');
        $this->sessionRepository->expects($this->once())->method('free')->with($mockExistingSession);

        $response = $this->handler->handle($request);

        $this->assertContains('session-2', $response->synced);
    }

    public function test_it_commits_when_sync_skipped_exception_is_thrown(): void
    {
        $sessionData = $this->getValidSessionData('session-old');
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [$sessionData]);

        // Posem una sessió existent perquè puguem testejar que s'allibera del Catch
        $mockExistingSession = $this->createMock(Session::class);
        $mockSessionId = $this->createMock(SessionId::class);
        $mockSessionId->method('value')->willReturn('session-old');
        $mockExistingSession->method('id')->willReturn($mockSessionId);

        $this->sessionRepository->method('findByIds')->willReturn([$mockExistingSession]);

        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->willThrowException(new SyncSkippedException());

        $this->transactionManager->expects($this->once())->method('begin');
        $this->sessionRepository->expects($this->never())->method('save'); // No guardem
        $this->transactionManager->expects($this->once())->method('commit'); // PERÒ FEM COMMIT

        // Es fa el free en el Catch
        $this->sessionRepository->expects($this->once())->method('free')->with($mockExistingSession);

        $response = $this->handler->handle($request);

        $this->assertContains('session-old', $response->synced);
        $this->assertEmpty($response->failed);
    }

    public function test_it_rollbacks_and_adds_to_failed_on_domain_exception(): void
    {
        $sessionData = $this->getValidSessionData('session-fail');
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [$sessionData]);

        $this->sessionRepository->method('findByIds')->willReturn([]);

        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->willThrowException(new SyncValidationException('missing_listening_point'));

        $this->transactionManager->expects($this->once())->method('begin');
        $this->transactionManager->expects($this->once())->method('rollback'); // FEM ROLLBACK
        $this->transactionManager->expects($this->never())->method('commit');

        $response = $this->handler->handle($request);

        $this->assertEmpty($response->synced);
        $this->assertCount(1, $response->failed);
        $this->assertEquals('session-fail', $response->failed[0]['sessionId']);
        $this->assertEquals('missing_listening_point', $response->failed[0]['reason']);
    }

    public function test_it_rollbacks_on_unexpected_throwable_and_returns_internal_error(): void
    {
        $sessionData = $this->getValidSessionData('session-error');
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [$sessionData]);

        $this->sessionRepository->method('findByIds')->willReturn([]);

        // Simulem un error fatal com una caiguda de la base de dades
        $this->synchronizer->expects($this->once())
            ->method('sync')
            ->willThrowException(new \RuntimeException('Database is gone'));

        $this->transactionManager->expects($this->once())->method('rollback');

        $response = $this->handler->handle($request);

        $this->assertEmpty($response->synced);
        $this->assertCount(1, $response->failed);
        $this->assertEquals('session-error', $response->failed[0]['sessionId']);
        $this->assertEquals('internal_error', $response->failed[0]['reason']); // Missatge sanititzat
    }

    public function test_partial_success_with_multiple_sessions(): void
    {
        $request = new SyncUpRequest('550e8400-e29b-41d4-a716-446655440001', [
            $this->getValidSessionData('session-good-1'), // OK
            $this->getValidSessionData('session-bad'),    // Fail Domain
            $this->getValidSessionData('session-good-2'), // OK
        ]);

        $this->sessionRepository->method('findByIds')->willReturn([]);
        $this->sessionRepository->method('findByCensusIdsAndAnnotator')->willReturn([]);
        $this->profileRepository->method('findValidOrnithologistCodes')->willReturn([]);
        $this->birdRepository->method('findValidBirdIds')->willReturn([]);
        $this->censusRepository->method('findCensusIdsByStatus')->willReturn([]);

        $mockSession = $this->createMock(Session::class);

        // Configurem el Synchronizer per fallar només a la segona trucada
        $this->synchronizer->expects($this->exactly(3))
            ->method('sync')
            ->willReturnCallback(function (SessionSyncDto $dto) use ($mockSession) {
                if ($dto->id === 'session-bad') {
                    throw new \InvalidArgumentException('Bad Data');
                }
                return $mockSession;
            });

        $this->transactionManager->expects($this->exactly(3))->method('begin');
        $this->transactionManager->expects($this->exactly(2))->method('commit');
        $this->transactionManager->expects($this->once())->method('rollback');

        $this->sessionRepository->expects($this->exactly(2))->method('save')->with($mockSession);
        // Alliberem els 2 bons creats. El dolent ha llançat error abans de crear-se l'entitat
        $this->sessionRepository->expects($this->exactly(2))->method('free')->with($mockSession);

        $response = $this->handler->handle($request);

        $this->assertCount(2, $response->synced);
        $this->assertContains('session-good-1', $response->synced);
        $this->assertContains('session-good-2', $response->synced);

        $this->assertCount(1, $response->failed);
        $this->assertEquals('session-bad', $response->failed[0]['sessionId']);
        $this->assertEquals('Bad Data', $response->failed[0]['reason']);
    }

    public function test_it_fails_when_session_for_census_already_exists_with_different_id(): void
    {
        $censusId = '220e8400-e29b-41d4-a716-446655440002';
        $annotatorId = '110e8400-e29b-41d4-a716-446655440002';
        $existingSessionId = '550e8400-e29b-41d4-a716-446655440001';
        $incomingSessionId = '550e8400-e29b-41d4-a716-446655440099';

        // 1. Dades de la sessió "Nova" que envia el mòbil
        $sessionData = $this->getValidSessionData($incomingSessionId);
        $sessionData['censusId'] = $censusId;
        $sessionData['sessionType'] = 1; // Census

        $request = new SyncUpRequest($annotatorId, [$sessionData]);

        // 2. Creem una sessió REAL (Sessió "A") que ja existeix a la BD
        // IMPORTANT: Fem servir l'entitat real perquè el Handler pugui llegir id() i censusId()
        $existingSessionInDb = $this->createSessionEntity($existingSessionId, $annotatorId, $censusId);

        // Configurem el repositori perquè retorni aquesta sessió real
        $this->sessionRepository->expects($this->once())
            ->method('findByCensusIdsAndAnnotator')
            ->willReturn([$existingSessionInDb]);

        $this->sessionRepository->method('findByIds')->willReturn([]);
        $this->profileRepository->method('findValidOrnithologistCodes')->willReturn([]);

        // 3. Verifiquem el flux de transaccions: begin -> excepció -> rollback
        $this->transactionManager->expects($this->once())->method('begin');
        $this->transactionManager->expects($this->once())->method('rollback');
        $this->transactionManager->expects($this->never())->method('commit');

        // El synchronizer no s'ha de cridar mai
        $this->synchronizer->expects($this->never())->method('sync');

        // Act
        $response = $this->handler->handle($request);

        // Assert
        $this->assertEmpty($response->synced);
        $this->assertCount(1, $response->failed);
        $this->assertEquals($incomingSessionId, $response->failed[0]['sessionId']);
        $this->assertEquals('duplicate_session_for_census', $response->failed[0]['reason']);
    }

    // --- Helpers ---

    private function getValidSessionData(string $id): array
    {
        return [
            'id' => $id,
            'censusId' => null,
            'sessionType' => 0, // Incidental
            'startTime' => '2023-01-01T10:00:00Z',
            'endTime' => '2023-01-01T12:00:00Z',
            'temperature' => 1,
            'wind' => 0,
            'sky' => 0,
            'rain' => 0,
            'visibility' => 2,
            'moon' => 0,
            'listeningPoint' => null,
            'latitude' => null,
            'longitude' => null,
            'accuracy' => null,
            'altitude' => null,
            'altitudeAccuracy' => null,
            'locationSource' => null,
            'createdAt' => '2023-01-01T10:00:00Z',
            'updatedAt' => '2023-01-01T10:00:00Z',
            'observations' => [],
            'participants' => [
                ['ornithologistCode' => 'ORN-1']
            ]
        ];
    }

    private function createSessionEntity(string $id, string $annotatorId, ?string $censusId = null): Session
    {
        return Session::create(
            id: new \App\Observation\Domain\ValueObject\SessionId($id),
            censusId: $censusId ? new \App\Census\Domain\ValueObject\CensusId($censusId) : null,
            annotatorId: new \App\Auth\Domain\ValueObject\AuthUserId($annotatorId),
            sessionType: \App\Observation\Domain\ValueObject\SessionType::Census,
            startTime: new \DateTimeImmutable('2023-01-01 10:00:00'),
            endTime: new \DateTimeImmutable('2023-01-01 12:00:00'),
            temperature: \App\Observation\Domain\ValueObject\TemperatureRange::TenToTwenty,
            wind: \App\Observation\Domain\ValueObject\WindLevel::Calm,
            sky: \App\Observation\Domain\ValueObject\SkyCondition::Clear,
            rain: \App\Observation\Domain\ValueObject\RainLevel::None,
            visibility: \App\Observation\Domain\ValueObject\VisibilityLevel::Good,
            moon: \App\Observation\Domain\ValueObject\MoonPresence::Absent,
            listeningPoint: new \App\Observation\Domain\ValueObject\ListeningPoint(1),
            latitude: new \App\Shared\Domain\ValueObject\Latitude(41.0),
            longitude: new \App\Shared\Domain\ValueObject\Longitude(2.0),
            accuracy: null, altitude: null, altitudeAccuracy: null,
            locationSource: \App\Observation\Domain\ValueObject\LocationSource::GPS,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }
}
