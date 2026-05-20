<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use App\Analytics\Infrastructure\EventListener\AnalyticsPurgeAdjustmentListener;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AnalyticsPurgeAdjustmentListenerTest extends TestCase
{
    private CronStateRepositoryInterface&MockObject        $cronStateRepository;
    private AnalyticsSessionQueryInterface&MockObject      $sessionQuery;
    private MapBubbleSummaryRepositoryInterface&MockObject $mapBubbleRepository;

    protected function setUp(): void
    {
        $this->cronStateRepository = $this->createMock(CronStateRepositoryInterface::class);
        $this->sessionQuery        = $this->createMock(AnalyticsSessionQueryInterface::class);
        $this->mapBubbleRepository = $this->createMock(MapBubbleSummaryRepositoryInterface::class);
    }

    private function listener(int $precision = 2): AnalyticsPurgeAdjustmentListener
    {
        return new AnalyticsPurgeAdjustmentListener(
            $this->cronStateRepository,
            $this->sessionQuery,
            $this->mapBubbleRepository,
            $precision,
            new GroupDataPointsService(),
        );
    }

    public function test_does_nothing_when_no_cron_state_exists(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);

        $this->sessionQuery->expects(self::never())->method('findDeletedDataPoints');
        $this->mapBubbleRepository->expects(self::never())->method('upsertBatch');

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_queries_deleted_data_points_with_last_processed_at(): void
    {
        $time  = new DateTimeImmutable('2026-05-01T00:00:00Z', new DateTimeZone('UTC'));
        $state = new CronState(CronJobName::UpdateAnalytics, $time);

        $this->cronStateRepository->method('findByJobName')->willReturn($state);

        $this->sessionQuery->expects(self::once())
            ->method('findDeletedDataPoints')
            ->with(self::callback(fn(DateTimeImmutable $dt) => $dt == $time))
            ->willReturn([]);

        $this->mapBubbleRepository->method('upsertBatch');

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_groups_deleted_points_and_calls_upsert(): void
    {
        $this->cronStateRepository->method('findByJobName')
            ->willReturn(new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0')));

        $this->sessionQuery->method('findDeletedDataPoints')->willReturn([
            new ObservationDataPoint('sp-1', 41.38, 2.17, 2026, 5, -1),
            new ObservationDataPoint('sp-1', 41.38, 2.17, 2026, 5, -1),
        ]);

        $this->mapBubbleRepository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === -2;
            }));

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_empty_deleted_points_calls_upsert_with_empty_array(): void
    {
        $this->cronStateRepository->method('findByJobName')
            ->willReturn(new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0')));

        $this->sessionQuery->method('findDeletedDataPoints')->willReturn([]);

        $this->mapBubbleRepository->expects(self::once())
            ->method('upsertBatch')
            ->with([]);

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_coordinates_are_rounded_to_precision(): void
    {
        $this->cronStateRepository->method('findByJobName')
            ->willReturn(new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0')));

        $this->sessionQuery->method('findDeletedDataPoints')->willReturn([
            new ObservationDataPoint('sp-1', 41.38765, 2.17432, 2026, 5, -1),
        ]);

        $this->mapBubbleRepository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1
                    && $rows[0]['lat'] === round(41.38765, 2)
                    && $rows[0]['lon'] === round(2.17432, 2);
            }));

        $this->listener(2)->__invoke(new BeforeSessionsPurgedEvent());
    }
}
