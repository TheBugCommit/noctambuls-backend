<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use App\Analytics\Infrastructure\EventListener\MapBubbleSummaryListener;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MapBubbleSummaryListenerTest extends TestCase
{
    private MapBubbleSummaryRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MapBubbleSummaryRepositoryInterface::class);
    }

    private function listener(int $precision = 2): MapBubbleSummaryListener
    {
        return new MapBubbleSummaryListener($this->repository, $precision, new GroupDataPointsService());
    }

    private function event(ObservationDataPoint ...$points): AnalyticsDataCollectedEvent
    {
        return new AnalyticsDataCollectedEvent(array_values($points));
    }

    private function point(string $species, float $lat, float $lon, int $year, int $delta = 1, int $month = 5): ObservationDataPoint
    {
        return new ObservationDataPoint($species, $lat, $lon, $year, $month, $delta);
    }

    public function test_empty_event_calls_upsert_with_empty_array(): void
    {
        $this->repository->expects(self::once())->method('upsertBatch')->with([]);

        $this->listener()->__invoke($this->event());
    }

    public function test_single_positive_delta_creates_one_row_with_count_one(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === 1;
            }));

        $this->listener()->__invoke($this->event($this->point('sp-1', 41.38, 2.17, 2026, 1)));
    }

    public function test_single_negative_delta_creates_one_row_with_count_minus_one(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === -1;
            }));

        $this->listener()->__invoke($this->event($this->point('sp-1', 41.38, 2.17, 2026, -1)));
    }

    public function test_two_points_same_group_positive_and_negative_cancel(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === 0;
            }));

        $this->listener()->__invoke($this->event(
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
            $this->point('sp-1', 41.38, 2.17, 2026, -1),
        ));
    }

    public function test_points_in_different_groups_produce_separate_rows(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(fn(array $rows) => count($rows) === 2));

        $this->listener()->__invoke($this->event(
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
            $this->point('sp-2', 41.38, 2.17, 2026, 1),
        ));
    }

    public function test_coordinates_are_rounded_to_precision(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1
                    && $rows[0]['lat'] === round(41.38765, 2)
                    && $rows[0]['lon'] === round(2.17432, 2);
            }));

        $this->listener(2)->__invoke($this->event($this->point('sp-1', 41.38765, 2.17432, 2026, 1)));
    }

    public function test_observations_with_same_rounded_coords_are_grouped(): void
    {
        // 41.381 and 41.382 both round to 41.38 at precision=2
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === 2;
            }));

        $this->listener(2)->__invoke($this->event(
            $this->point('sp-1', 41.381, 2.17, 2026, 1),
            $this->point('sp-1', 41.382, 2.17, 2026, 1),
        ));
    }

    public function test_restore_delta_adds_back_after_subtract(): void
    {
        // subtract (-1) from a previous cron run followed by restore (+1) in current run: net=0
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(function (array $rows) {
                return count($rows) === 1 && $rows[0]['count'] === 0;
            }));

        $this->listener()->__invoke($this->event(
            $this->point('sp-1', 41.38, 2.17, 2026, -1), // subtract (cron detected delete)
            $this->point('sp-1', 41.38, 2.17, 2026, 1),  // restore (cron detected restore)
        ));
    }

    public function test_different_years_for_same_species_create_separate_rows(): void
    {
        $this->repository->expects(self::once())
            ->method('upsertBatch')
            ->with(self::callback(fn(array $rows) => count($rows) === 2));

        $this->listener()->__invoke($this->event(
            $this->point('sp-1', 41.38, 2.17, 2025, 1),
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
        ));
    }
}
