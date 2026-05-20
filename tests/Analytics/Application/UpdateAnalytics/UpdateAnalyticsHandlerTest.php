<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Application\UpdateAnalytics;

use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsHandler;
use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Service\AnalyticsEventDispatcherInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use App\Shared\Domain\Service\TransactionManagerInterface;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateAnalyticsHandlerTest extends TestCase
{
    private CronStateRepositoryInterface&MockObject     $cronStateRepository;
    private AnalyticsSessionQueryInterface&MockObject   $sessionQuery;
    private AnalyticsEventDispatcherInterface&MockObject $eventDispatcher;
    private TransactionManagerInterface&MockObject      $transactionManager;
    private UpdateAnalyticsHandler                      $handler;

    protected function setUp(): void
    {
        $this->cronStateRepository = $this->createMock(CronStateRepositoryInterface::class);
        $this->sessionQuery        = $this->createMock(AnalyticsSessionQueryInterface::class);
        $this->eventDispatcher     = $this->createMock(AnalyticsEventDispatcherInterface::class);
        $this->transactionManager  = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new UpdateAnalyticsHandler(
            $this->cronStateRepository,
            $this->sessionQuery,
            $this->eventDispatcher,
            $this->transactionManager,
        );
    }

    public function test_uses_epoch_when_no_cron_state_exists(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);

        $this->sessionQuery->expects(self::once())
            ->method('findDataPointsSince')
            ->with(self::callback(
                fn(DateTimeImmutable $dt) => $dt->getTimestamp() === 0
            ))
            ->willReturn([]);

        $this->handler->handle();
    }

    public function test_uses_existing_last_processed_at(): void
    {
        $time  = new DateTimeImmutable('2026-05-01T00:00:00Z', new DateTimeZone('UTC'));
        $state = new CronState(CronJobName::UpdateAnalytics, $time);

        $this->cronStateRepository->method('findByJobName')->willReturn($state);

        $this->sessionQuery->expects(self::once())
            ->method('findDataPointsSince')
            ->with(self::callback(fn(DateTimeImmutable $dt) => $dt == $time))
            ->willReturn([]);

        $this->handler->handle();
    }

    public function test_dispatches_event_with_loaded_data_points(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);

        $points = [
            new ObservationDataPoint('species-1', 41.38, 2.17, 2026, 5, 1),
            new ObservationDataPoint('species-2', 40.00, 1.00, 2026, 5, -1),
        ];
        $this->sessionQuery->method('findDataPointsSince')->willReturn($points);

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (AnalyticsDataCollectedEvent $event) use ($points) {
                return $event->dataPoints() === $points;
            }));

        $this->handler->handle();
    }

    public function test_saves_updated_cron_state(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->sessionQuery->method('findDataPointsSince')->willReturn([]);

        $this->cronStateRepository->expects(self::once())->method('save')
            ->with(self::isInstanceOf(CronState::class));

        $this->handler->handle();
    }

    public function test_commits_after_event_and_save(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->sessionQuery->method('findDataPointsSince')->willReturn([]);

        $order   = [];
        $this->transactionManager->method('begin')->willReturnCallback(function () use (&$order) { $order[] = 'begin'; });
        $this->eventDispatcher->method('dispatch')->willReturnCallback(function () use (&$order) { $order[] = 'dispatch'; });
        $this->cronStateRepository->method('save')->willReturnCallback(function () use (&$order) { $order[] = 'save'; });
        $this->transactionManager->method('commit')->willReturnCallback(function () use (&$order) { $order[] = 'commit'; });

        $this->handler->handle();

        self::assertSame(['begin', 'dispatch', 'save', 'commit'], $order);
    }

    public function test_rolls_back_and_rethrows_on_dispatcher_failure(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->sessionQuery->method('findDataPointsSince')->willReturn([]);

        $exception = new \RuntimeException('dispatcher failed');
        $this->eventDispatcher->method('dispatch')->willThrowException($exception);

        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dispatcher failed');

        $this->handler->handle();
    }

    public function test_rolls_back_and_rethrows_on_save_failure(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->sessionQuery->method('findDataPointsSince')->willReturn([]);

        $exception = new \RuntimeException('save failed');
        $this->cronStateRepository->method('save')->willThrowException($exception);

        $this->transactionManager->expects(self::once())->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->handler->handle();
    }

    public function test_response_contains_data_point_count(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $points = [
            new ObservationDataPoint('s1', 41.0, 2.0, 2026, 5, 1),
            new ObservationDataPoint('s2', 42.0, 3.0, 2026, 5, -1),
            new ObservationDataPoint('s3', 43.0, 4.0, 2026, 5, 1),
        ];
        $this->sessionQuery->method('findDataPointsSince')->willReturn($points);

        $response = $this->handler->handle();

        self::assertSame(3, $response->dataPointCount);
    }

    public function test_response_processed_at_is_utc_iso8601(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->sessionQuery->method('findDataPointsSince')->willReturn([]);

        $response = $this->handler->handle();

        self::assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            $response->processedAt,
        );
    }
}
