<?php

declare(strict_types=1);

namespace App\Analytics\Application\UpdateAnalytics;

final readonly class UpdateAnalyticsResponse
{
    public function __construct(
        public string $processedAt,
        public int    $dataPointCount,
    ) {}
}
