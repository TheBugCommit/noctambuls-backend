<?php

namespace App\Analytics\Application\Mapper;

use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsResponse;

class DashboardResponseMapper
{
    private const array COLOR_PALETTE = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
        '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16',
    ];

    public static function toDashboardResponse(
        \DateTimeImmutable $lastProcessedAt,
        array              $availableYears,
        int                $selectedYear,
        array              $chartRows
    ): GetDashboardAnalyticsResponse
    {
        return new GetDashboardAnalyticsResponse(
            $lastProcessedAt,
            $availableYears,
            $selectedYear,
            self::buildChartData($chartRows)
        );
    }


    private static function buildChartData(array $rows): array
    {
        $species = [];
        $colorIndex = 0;

        foreach ($rows as $row) {
            $id = $row['species_id'];
            if (!isset($species[$id])) {
                $species[$id] = [
                    'name' => $row['species_name'],
                    'color' => self::COLOR_PALETTE[$colorIndex % count(self::COLOR_PALETTE)],
                    'data' => array_fill(0, 12, 0),
                ];
                ++$colorIndex;
            }
            $species[$id]['data'][$row['month'] - 1] = $row['count'];
        }

        return ['series' => array_values($species)];
    }
}

