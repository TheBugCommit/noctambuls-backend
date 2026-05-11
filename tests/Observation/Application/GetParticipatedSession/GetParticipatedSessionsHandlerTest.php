<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\GetParticipatedSessions;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsHandler;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsRequest;
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
use App\Profile\Domain\ValueObject\OrnithologistCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetParticipatedSessionsHandlerTest extends TestCase
{
    private SessionRepositoryInterface&MockObject $sessionRepository;
    private GetParticipatedSessionsHandler $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->handler = new GetParticipatedSessionsHandler($this->sessionRepository);
    }

    public function test_it_returns_empty_response_when_total_is_zero(): void
    {
        // 1. Preparem la Request amb format de codi d'ornitòleg correcte
        $request = new GetParticipatedSessionsRequest(
            ornithologistCode: '#OR-N-0001',
            offset: 0,
            limit: 10
        );

        $this->sessionRepository->expects($this->once())
            ->method('countByParticipantCode')
            ->with($this->callback(fn (OrnithologistCode $code) => $code->value() === '#OR-N-0001'))
            ->willReturn(0);

        $this->sessionRepository->expects($this->never())->method('findPaginatedIdsByParticipantCode');
        $this->sessionRepository->expects($this->never())->method('findByIdsWithRelations');

        $response = $this->handler->handle($request);

        $this->assertEmpty($response->sessions);
        $this->assertEquals(0, $response->meta['totalItems']);
        $this->assertEquals(1, $response->meta['currentPage']);
        $this->assertFalse($response->meta['hasMore']);
    }

    public function test_it_returns_paginated_sessions_successfully(): void
    {
        $request = new GetParticipatedSessionsRequest(
            ornithologistCode: '#OR-N-0002',
            offset: 10,
            limit: 10
        );

        $this->sessionRepository->expects($this->once())
            ->method('countByParticipantCode')
            ->willReturn(15);

        // UUIDs simulats des de la base de dades
        $uuid1 = '550e8400-e29b-41d4-a716-446655440011';
        $uuid2 = '550e8400-e29b-41d4-a716-446655440012';

        $this->sessionRepository->expects($this->once())
            ->method('findPaginatedIdsByParticipantCode')
            ->with(
                $this->callback(fn (OrnithologistCode $code) => $code->value() === '#OR-N-0002'),
                10,
                10
            )
            ->willReturn([
                ['id' => $uuid1],
                ['id' => $uuid2]
            ]);

        $session1 = $this->createDummySession($uuid1);
        $session2 = $this->createDummySession($uuid2);

        $this->sessionRepository->expects($this->once())
            ->method('findByIdsWithRelations')
            ->with([$uuid1, $uuid2])
            ->willReturn([$session1, $session2]);

        $response = $this->handler->handle($request);

        $this->assertCount(2, $response->sessions);
        $this->assertEquals($uuid1, $response->sessions[0]['id']);
        $this->assertEquals($uuid2, $response->sessions[1]['id']);

        $this->assertEquals(15, $response->meta['totalItems']);
        $this->assertEquals(2, $response->meta['currentPage']);
        $this->assertEquals(2, $response->meta['totalPages']);
        $this->assertFalse($response->meta['hasMore']);
    }

    // --- HELPER BÀSIC ---

    private function createDummySession(string $uuid): Session
    {
        return Session::create(
            id: new SessionId($uuid),
            censusId: null,
            annotatorId: new AuthUserId('550e8400-e29b-41d4-a716-446655440123'),
            sessionType: SessionType::Incidental,
            startTime: new \DateTimeImmutable('2023-01-01 10:00:00'),
            endTime: new \DateTimeImmutable('2023-01-01 12:00:00'),
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
