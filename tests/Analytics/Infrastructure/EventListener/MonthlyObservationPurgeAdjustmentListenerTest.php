<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use App\Analytics\Infrastructure\EventListener\MonthlyObservationPurgeAdjustmentListener;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MonthlyObservationPurgeAdjustmentListenerTest extends TestCase
{
    private CronStateRepositoryInterface&MockObject                 $cronStateRepository;
    private AnalyticsSessionQueryInterface&MockObject               $sessionQuery;
    private MonthlyObservationSummaryRepositoryInterface&MockObject $monthlyRepository;

    protected function setUp(): void
    {
        $this->cronStateRepository = $this->createMock(CronStateRepositoryInterface::class);
        $this->sessionQuery        = $this->createMock(AnalyticsSessionQueryInterface::class);
        $this->monthlyRepository   = $this->createMock(MonthlyObservationSummaryRepositoryInterface::class);
    }

    private function listener(): MonthlyObservationPurgeAdjustmentListener
    {
        return new MonthlyObservationPurgeAdjustmentListener(
            $this->cronStateRepository,
            $this->sessionQuery,
            $this->monthlyRepository,
            new GroupDataPointsService(),
        );
    }

    private function point(string $species, int $year, int $month, int $delta = 1): ObservationDataPoint
    {
        return new ObservationDataPoint($species, 0.0, 0.0, $year, $month, $delta);
    }

    public function test_does_nothing_when_no_cron_state_exists(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);

        $this->sessionQuery->expects(self::never())->method('findDeletedDataPoints');
        $this->monthlyRepository->expects(self::never())->method('upsertBatch');

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

        $this->monthlyRepository->method('upsertBatch');

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_groups_and_upserts_negative_counts(): void
    {
        $this->cronStateRepository->method('findByJobName')
            ->willReturn(new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0')));

        $this->sessionQuery->method('findDeletedDataPoints')->willReturn([
            $this->point('sp-1', 2026, 5, -1),
            $this->point('sp-1', 2026, 5, -1),
        ]);

        $this->monthlyRepository->expects(self::once())
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

        $this->monthlyRepository->expects(self::once())
            ->method('upsertBatch')
            ->with([]);

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }

    public function test_zero_count_rows_are_filtered_out(): void
    {
        $this->cronStateRepository->method('findByJobName')
            ->willReturn(new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0')));

        $this->sessionQuery->method('findDeletedDataPoints')->willReturn([
            $this->point('sp-1', 2026, 5, 1),
            $this->point('sp-1', 2026, 5, -1),
        ]);

        $this->monthlyRepository->expects(self::once())
            ->method('upsertBatch')
            ->with([]);

        $this->listener()->__invoke(new BeforeSessionsPurgedEvent());
    }
}
