<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Infrastructure\EventListener\MonthlyObservationSummaryListener;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MonthlyObservationSummaryListenerTest extends TestCase
{
    private MonthlyObservationSummaryRepositoryInterface&MockObject $repository;
    private MonthlyObservationSummaryListener $listener;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MonthlyObservationSummaryRepositoryInterface::class);
        $this->listener   = new MonthlyObservationSummaryListener($this->repository, new GroupDataPointsService());
    }

    private function event(ObservationDataPoint ...$points): AnalyticsDataCollectedEvent
    {
        return new AnalyticsDataCollectedEvent(array_values($points));
    }

    private function point(string $species, int $year, int $month, int $delta = 1): ObservationDataPoint
    {
        return new ObservationDataPoint($species, 0.0, 0.0, $year, $month, $delta);
    }

    public function test_empty_event_calls_upsert_with_empty_array(): void
    {
        $this->repository->expects(self::once())->method('upsertBatch')->with([]);

        ($this->listener)($this->event());
    }

    public function test_single_point_creates_one_row_with_correct_fields(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1
                    && $rows[0]['speciesId'] === 'sp-1'
                    && $rows[0]['year'] === 2026
                    && $rows[0]['month'] === 3
                    && $rows[0]['count'] === 1;
            }));

        ($this->listener)($this->event($this->point('sp-1', 2026, 3, 1)));
    }

    public function test_two_points_same_group_sums_counts(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === 2;
            }));

        ($this->listener)($this->event(
            $this->point('sp-1', 2026, 3, 1),
            $this->point('sp-1', 2026, 3, 1),
        ));
    }

    public function test_zero_count_rows_are_filtered_out(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with([]);

        ($this->listener)($this->event(
            $this->point('sp-1', 2026, 3, 1),
            $this->point('sp-1', 2026, 3, -1),
        ));
    }

    public function test_different_months_create_separate_rows(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(fn(array $rows) => count($rows) === 2));

        ($this->listener)($this->event(
            $this->point('sp-1', 2026, 3, 1),
            $this->point('sp-1', 2026, 4, 1),
        ));
    }

    public function test_different_species_create_separate_rows(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(fn(array $rows) => count($rows) === 2));

        ($this->listener)($this->event(
            $this->point('sp-1', 2026, 3, 1),
            $this->point('sp-2', 2026, 3, 1),
        ));
    }

    public function test_different_years_create_separate_rows(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(fn(array $rows) => count($rows) === 2));

        ($this->listener)($this->event(
            $this->point('sp-1', 2025, 3, 1),
            $this->point('sp-1', 2026, 3, 1),
        ));
    }

    public function test_negative_delta_point_creates_row_with_negative_count(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === -1;
            }));

        ($this->listener)($this->event($this->point('sp-1', 2026, 3, -1)));
    }

    public function test_row_structure_has_species_year_month_count_keys(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return isset($rows[0]['speciesId'], $rows[0]['year'], $rows[0]['month'], $rows[0]['count']);
            }));

        ($this->listener)($this->event($this->point('sp-1', 2026, 3, 1)));
    }
}
