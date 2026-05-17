<?php
declare(strict_types=1);

namespace App\Analytics\Application\GetDashboardAnalytics;

final class GetDashboardAnalyticsRequest
{
    public function __construct(
        public int $requestedYear
    )
    {
    }
}
