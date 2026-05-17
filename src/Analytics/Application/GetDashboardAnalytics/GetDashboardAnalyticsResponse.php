<?php
declare(strict_types=1);

namespace App\Analytics\Application\GetDashboardAnalytics;

final class GetDashboardAnalyticsResponse
{
    public function __construct(
        public ?\DateTimeImmutable $lastProcessedAt,
        public array               $availableYears,
        public int                 $selectedYear,
        public array               $chartData,
        public array               $percentageData,
    ) {}
}
