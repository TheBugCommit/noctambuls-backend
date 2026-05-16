<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Event;

final class AnalyticsDataCollectedEvent
{
    /** @param list<ObservationDataPoint> $dataPoints */
    public function __construct(private readonly array $dataPoints) {}

    /** @return list<ObservationDataPoint> */
    public function dataPoints(): array
    {
        return $this->dataPoints;
    }
}
