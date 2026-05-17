<?php

declare(strict_types=1);

namespace App\Analytics\Application\Mapper;

use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsResponse;

final class DashboardResponseMapper
{
    private const array COLOR_PALETTE = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
        '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16',
    ];

    public static function toDashboardResponse(
        ?\DateTimeImmutable $lastProcessedAt,
        array               $availableYears,
        int                 $selectedYear,
        array               $chartRows,
    ): GetDashboardAnalyticsResponse {
        return new GetDashboardAnalyticsResponse(
            $lastProcessedAt,
            $availableYears,
            $selectedYear,
            self::buildChartData($chartRows),
            self::buildPercentageData($chartRows),
        );
    }

    private static function buildChartData(array $rows): array
    {
        $species    = [];
        $colorIndex = 0;

        foreach ($rows as $row) {
            $id = $row['species_id'];
            if (!isset($species[$id])) {
                $species[$id] = [
                    'name'  => $row['species_name'],
                    'color' => self::COLOR_PALETTE[$colorIndex % count(self::COLOR_PALETTE)],
                    'data'  => array_fill(0, 12, 0),
                ];
                ++$colorIndex;
            }
            $species[$id]['data'][$row['month'] - 1] = $row['count'];
        }

        return ['series' => array_values($species)];
    }

    private static function buildPercentageData(array $rows): array
    {
        $totals     = [];
        $names      = [];
        $colors     = [];
        $totalCount = 0;
        $colorIndex = 0;

        foreach ($rows as $row) {
            $id = $row['species_id'];
            if (!isset($totals[$id])) {
                $totals[$id] = 0;
                $names[$id]  = $row['species_name'];
                $colors[$id] = self::COLOR_PALETTE[$colorIndex % count(self::COLOR_PALETTE)];
                ++$colorIndex;
            }
            $totals[$id] += $row['count'];
            $totalCount  += $row['count'];
        }

        if ($totalCount === 0) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }

        arsort($totals);

        return [
            'labels' => array_map(fn($id) => $names[$id],  array_keys($totals)),
            'data'   => array_map(fn($c)  => round($c / $totalCount * 100, 1), array_values($totals)),
            'colors' => array_map(fn($id) => $colors[$id], array_keys($totals)),
        ];
    }
}
