<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\PurgeDeletedSessions;

use App\Observation\Application\PurgeDeletedSessions\PurgeDeletedSessionsHandler;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\Service\SessionEventPublisherInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PurgeDeletedSessionsHandlerTest extends TestCase
{
    private SessionRepositoryInterface&MockObject  $sessionRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private SessionEventPublisherInterface&MockObject $eventPublisher;
    private PurgeDeletedSessionsHandler              $handler;

    protected function setUp(): void
    {
        $this->sessionRepository  = $this->createMock(SessionRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->eventPublisher     = $this->createMock(SessionEventPublisherInterface::class);
        $this->handler            = new PurgeDeletedSessionsHandler(
            $this->sessionRepository,
            $this->transactionManager,
            $this->eventPublisher,
        );
    }

    public function test_handle_returns_purged_count(): void
    {
        $this->sessionRepository->method('purgeDeleted')->willReturn(3);
        $this->eventPublisher->expects(self::once())->method('publishBeforeSessionsPurged');
        $this->transactionManager->expects(self::once())->method('commit');

        $count = $this->handler->handle();

        self::assertSame(3, $count);
    }

    public function test_handle_publishes_event_before_purging(): void
    {
        $callOrder = [];

        $this->eventPublisher
            ->expects(self::once())
            ->method('publishBeforeSessionsPurged')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'publish'; });

        $this->sessionRepository
            ->expects(self::once())
            ->method('purgeDeleted')
            ->willReturnCallback(function () use (&$callOrder) { $callOrder[] = 'purge'; return 0; });

        $this->handler->handle();

        self::assertSame(['publish', 'purge'], $callOrder);
    }

    public function test_handle_rolls_back_on_exception(): void
    {
        $this->eventPublisher
            ->method('publishBeforeSessionsPurged')
            ->willThrowException(new \RuntimeException('event error'));
        $this->transactionManager->expects(self::once())->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->handler->handle();
    }
}
