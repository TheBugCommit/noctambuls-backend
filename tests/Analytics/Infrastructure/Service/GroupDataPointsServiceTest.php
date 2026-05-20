<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Infrastructure\Service;

use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use PHPUnit\Framework\TestCase;

final class GroupDataPointsServiceTest extends TestCase
{
    private GroupDataPointsService $service;

    protected function setUp(): void
    {
        $this->service = new GroupDataPointsService();
    }

    private function point(string $species, float $lat, float $lon, int $year, int $delta = 1, int $month = 5): ObservationDataPoint
    {
        return new ObservationDataPoint($species, $lat, $lon, $year, $month, $delta);
    }

    public function test_group_bubble_returns_empty_for_empty_input(): void
    {
        self::assertSame([], $this->service->groupBubbleDataPoints([], 2));
    }

    public function test_group_bubble_single_point_creates_one_row_with_correct_fields(): void
    {
        $result = $this->service->groupBubbleDataPoints([$this->point('sp-1', 41.38, 2.17, 2026, 1)], 2);

        self::assertCount(1, $result);
        $row = array_values($result)[0];
        self::assertSame('sp-1', $row['speciesId']);
        self::assertSame(round(41.38, 2), $row['lat']);
        self::assertSame(round(2.17, 2), $row['lon']);
        self::assertSame(2026, $row['year']);
        self::assertSame(1, $row['count']);
    }

    public function test_group_bubble_same_key_sums_deltas(): void
    {
        $result = $this->service->groupBubbleDataPoints([
            $this->point('sp-1', 41.38, 2.17, 2026, 2),
            $this->point('sp-1', 41.38, 2.17, 2026, 3),
        ], 2);

        self::assertCount(1, $result);
        self::assertSame(5, array_values($result)[0]['count']);
    }

    public function test_group_bubble_different_species_creates_separate_rows(): void
    {
        $result = $this->service->groupBubbleDataPoints([
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
            $this->point('sp-2', 41.38, 2.17, 2026, 1),
        ], 2);

        self::assertCount(2, $result);
    }

    public function test_group_bubble_different_year_creates_separate_rows(): void
    {
        $result = $this->service->groupBubbleDataPoints([
            $this->point('sp-1', 41.38, 2.17, 2025, 1),
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
        ], 2);

        self::assertCount(2, $result);
    }

    public function test_group_bubble_rounds_coordinates_to_given_precision(): void
    {
        $result = $this->service->groupBubbleDataPoints([$this->point('sp-1', 41.38765, 2.17432, 2026, 1)], 2);

        $row = array_values($result)[0];
        self::assertSame(round(41.38765, 2), $row['lat']);
        self::assertSame(round(2.17432, 2), $row['lon']);
    }

    public function test_group_bubble_points_rounding_to_same_key_are_merged(): void
    {
        $result = $this->service->groupBubbleDataPoints([
            $this->point('sp-1', 41.381, 2.17, 2026, 1),
            $this->point('sp-1', 41.382, 2.17, 2026, 1),
        ], 2);

        self::assertCount(1, $result);
        self::assertSame(2, array_values($result)[0]['count']);
    }

    public function test_group_bubble_negative_delta_accumulated_correctly(): void
    {
        $result = $this->service->groupBubbleDataPoints([
            $this->point('sp-1', 41.38, 2.17, 2026, 1),
            $this->point('sp-1', 41.38, 2.17, 2026, -1),
        ], 2);

        self::assertSame(0, array_values($result)[0]['count']);
    }

    public function test_group_monthly_returns_empty_for_empty_input(): void
    {
        self::assertSame([], $this->service->groupMonthlyDataPoints([]));
    }

    public function test_group_monthly_single_point_creates_one_row(): void
    {
        $result = $this->service->groupMonthlyDataPoints([$this->point('sp-1', 0, 0, 2026, 1, 3)]);

        self::assertCount(1, $result);
        $row = array_values($result)[0];
        self::assertSame('sp-1', $row['speciesId']);
        self::assertSame(2026, $row['year']);
        self::assertSame(3, $row['month']);
        self::assertSame(1, $row['count']);
    }

    public function test_group_monthly_same_key_sums_deltas(): void
    {
        $result = $this->service->groupMonthlyDataPoints([
            $this->point('sp-1', 0, 0, 2026, 2, 5),
            $this->point('sp-1', 0, 0, 2026, 3, 5),
        ]);

        self::assertCount(1, $result);
        self::assertSame(5, array_values($result)[0]['count']);
    }

    public function test_group_monthly_different_months_create_separate_rows(): void
    {
        $result = $this->service->groupMonthlyDataPoints([
            $this->point('sp-1', 0, 0, 2026, 1, 3),
            $this->point('sp-1', 0, 0, 2026, 1, 4),
        ]);

        self::assertCount(2, $result);
    }

    public function test_group_monthly_different_species_create_separate_rows(): void
    {
        $result = $this->service->groupMonthlyDataPoints([
            $this->point('sp-1', 0, 0, 2026, 1, 5),
            $this->point('sp-2', 0, 0, 2026, 1, 5),
        ]);

        self::assertCount(2, $result);
    }

    public function test_group_monthly_different_years_create_separate_rows(): void
    {
        $result = $this->service->groupMonthlyDataPoints([
            $this->point('sp-1', 0, 0, 2025, 1, 5),
            $this->point('sp-1', 0, 0, 2026, 1, 5),
        ]);

        self::assertCount(2, $result);
    }
}
