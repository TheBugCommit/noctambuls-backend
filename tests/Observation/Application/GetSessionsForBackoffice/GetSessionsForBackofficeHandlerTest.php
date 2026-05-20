<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\GetSessionsForBackoffice;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Application\GetSessionsForBackoffice\GetSessionsForBackofficeHandler;
use App\Observation\Application\GetSessionsForBackoffice\GetSessionsForBackofficeRequest;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
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

final class GetSessionsForBackofficeHandlerTest extends TestCase
{
    private const string SESSION_UUID_1 = '550e8400-e29b-41d4-a716-446655440001';
    private const string SESSION_UUID_2 = '550e8400-e29b-41d4-a716-446655440002';
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';

    private SessionRepositoryInterface&MockObject $sessionRepository;
    private SessionHydrator&MockObject            $sessionHydrator;
    private GetSessionsForBackofficeHandler       $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->sessionHydrator   = $this->createMock(SessionHydrator::class);
        $this->sessionHydrator->method('hydrate')->willReturn(['profileMap' => [], 'censusMap' => []]);
        $this->handler           = new GetSessionsForBackofficeHandler($this->sessionRepository, $this->sessionHydrator);
    }

    public function test_handle_returns_empty_when_count_is_zero(): void
    {
        $this->sessionRepository->method('countForBackoffice')->willReturn(0);
        $this->sessionRepository->expects(self::never())->method('findPaginatedIdsForBackoffice');

        $response = $this->handler->handle(new GetSessionsForBackofficeRequest(page: 1, limit: 10));

        self::assertSame(0, $response->total);
        self::assertSame([], $response->sessions);
    }

    public function test_handle_returns_paginated_result(): void
    {
        $session1 = $this->createDummySession(self::SESSION_UUID_1);
        $session2 = $this->createDummySession(self::SESSION_UUID_2);

        $this->sessionRepository->method('countForBackoffice')->willReturn(2);
        $this->sessionRepository->method('findPaginatedIdsForBackoffice')
            ->willReturn([self::SESSION_UUID_1, self::SESSION_UUID_2]);
        $this->sessionRepository->method('findByIdsWithRelations')
            ->with([self::SESSION_UUID_1, self::SESSION_UUID_2])
            ->willReturn([$session1, $session2]);
        $this->sessionHydrator->method('hydrate')
            ->willReturn(['profileMap' => [], 'censusMap' => []]);

        $response = $this->handler->handle(new GetSessionsForBackofficeRequest(page: 1, limit: 10));

        self::assertSame(2, $response->total);
        self::assertCount(2, $response->sessions);
    }

    private function createDummySession(string $uuid): Session
    {
        return Session::create(
            id: new SessionId($uuid),
            censusId: null,
            annotatorId: new AuthUserId(self::ANNOTATOR_UUID),
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
            locationSource: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
