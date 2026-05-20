<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\RestoreSession;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Application\RestoreSession\RestoreSessionHandler;
use App\Observation\Application\RestoreSession\RestoreSessionRequest;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RestoreSessionHandlerTest extends TestCase
{
    private const string SESSION_UUID   = '550e8400-e29b-41d4-a716-446655440000';
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';

    private SessionRepositoryInterface&MockObject  $sessionRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private RestoreSessionHandler                  $handler;

    protected function setUp(): void
    {
        $this->sessionRepository  = $this->createMock(SessionRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->handler            = new RestoreSessionHandler($this->sessionRepository, $this->transactionManager);
    }

    public function test_handle_throws_when_session_not_found(): void
    {
        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([]);

        $this->expectException(SessionNotFoundException::class);
        $this->handler->handle(new RestoreSessionRequest(self::SESSION_UUID));
    }

    public function test_handle_restores_session_and_commits(): void
    {
        $session = $this->createDummySession();
        $session->markDeleted(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([$session]);
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->sessionRepository->expects(self::once())->method('save')->with($session);

        $this->handler->handle(new RestoreSessionRequest(self::SESSION_UUID));

        self::assertFalse($session->isDeleted());
    }

    public function test_handle_rolls_back_on_exception(): void
    {
        $session = $this->createDummySession();
        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([$session]);
        $this->sessionRepository->method('save')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->handler->handle(new RestoreSessionRequest(self::SESSION_UUID));
    }

    private function createDummySession(): Session
    {
        return Session::create(
            id: new SessionId(self::SESSION_UUID),
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
