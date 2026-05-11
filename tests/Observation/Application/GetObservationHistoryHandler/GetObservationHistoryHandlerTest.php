<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\GetObservationHistory;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryHandler;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryRequest;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetObservationHistoryHandlerTest extends TestCase
{
    private SessionRepositoryInterface&MockObject $sessionRepository;
    private GetObservationHistoryHandler $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->handler = new GetObservationHistoryHandler($this->sessionRepository);
    }

    public function test_it_returns_empty_response_when_user_has_no_history(): void
    {
        // Utilitzem un UUID vàlid per l'usuari buit
        $emptyUserId = '11111111-e29b-41d4-a716-446655440000';

        $request = new GetObservationHistoryRequest(
            annotatorId: $emptyUserId,
            offset: 0,
            limit: 20
        );

        $this->sessionRepository->expects($this->once())
            ->method('countByAnnotatorId')
            ->with($this->callback(fn (AuthUserId $id) => $id->value() === $emptyUserId))
            ->willReturn(0);

        $this->sessionRepository->expects($this->never())->method('findPaginatedIdsByAnnotatorId');
        $this->sessionRepository->expects($this->never())->method('findByIdsWithRelations');

        $response = $this->handler->handle($request);

        $this->assertEmpty($response->sessions);
        $this->assertEquals(0, $response->meta['totalItems']);
        $this->assertEquals(1, $response->meta['currentPage']);
        $this->assertFalse($response->meta['hasMore']);
    }

    public function test_it_returns_paginated_history_successfully(): void
    {
        // Utilitzem un UUID vàlid per a l'usuari actiu
        $activeUserId = '22222222-e29b-41d4-a716-446655440000';

        $request = new GetObservationHistoryRequest(
            annotatorId: $activeUserId,
            offset: 5,
            limit: 5
        );

        $this->sessionRepository->expects($this->once())
            ->method('countByAnnotatorId')
            ->with($this->callback(fn (AuthUserId $id) => $id->value() === $activeUserId))
            ->willReturn(12);

        // UUIDs simulats per a les sessions
        $sessionUuid1 = '550e8400-e29b-41d4-a716-446655440001';
        $sessionUuid2 = '550e8400-e29b-41d4-a716-446655440002';

        $this->sessionRepository->expects($this->once())
            ->method('findPaginatedIdsByAnnotatorId')
            ->with(
                $this->callback(fn (AuthUserId $id) => $id->value() === $activeUserId),
                5,
                5
            )
            ->willReturn([
                ['id' => $sessionUuid1],
                ['id' => $sessionUuid2]
            ]);

        $session1 = $this->createDummySession($sessionUuid1, $activeUserId);
        $session2 = $this->createDummySession($sessionUuid2, $activeUserId);

        $this->sessionRepository->expects($this->once())
            ->method('findByIdsWithRelations')
            ->with([$sessionUuid1, $sessionUuid2])
            ->willReturn([$session1, $session2]);

        $response = $this->handler->handle($request);

        $this->assertCount(2, $response->sessions);
        $this->assertEquals($sessionUuid1, $response->sessions[0]['id']);
        $this->assertEquals($activeUserId, $response->sessions[0]['annotatorId']);
        $this->assertIsArray($response->sessions[0]['observations']);
        $this->assertIsArray($response->sessions[0]['participants']);

        $this->assertEquals(12, $response->meta['totalItems']);
        $this->assertEquals(2, $response->meta['currentPage']);
        $this->assertEquals(3, $response->meta['totalPages']);
        $this->assertTrue($response->meta['hasMore']);
    }


    private function createDummySession(string $sessionUuid, string $annotatorUuid): Session
    {
        return Session::create(
            id: new SessionId($sessionUuid),
            censusId: null,
            annotatorId: new AuthUserId($annotatorUuid),
            sessionType: SessionType::Incidental,
            startTime: new \DateTimeImmutable('2023-10-25 08:00:00'),
            endTime: new \DateTimeImmutable('2023-10-25 10:00:00'),
            temperature: TemperatureRange::TenToTwenty,
            wind: WindLevel::Calm,
            sky: SkyCondition::Clear,
            rain: RainLevel::None,
            visibility: VisibilityLevel::Good,
            moon: MoonPresence::Absent,
            listeningPoint: null,
            latitude: null,
            longitude: null,
            accuracy: null,
            altitude: null,
            altitudeAccuracy: null,
            locationSource: LocationSource::GPS,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }
}
