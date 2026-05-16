<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;

final readonly class MapBubbleSummaryListener
{
    public function __construct(
        private MapBubbleSummaryRepositoryInterface $repository,
        private int $precision,
    ) {}

    public function __invoke(AnalyticsDataCollectedEvent $event): void
    {
        $grouped = [];

        foreach ($event->dataPoints() as $point) {
            $lat = round($point->lat, $this->precision);
            $lon = round($point->lon, $this->precision);
            $key = "{$point->speciesId}|{$lat}|{$lon}|{$point->year}";

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'speciesId' => $point->speciesId,
                    'lat'       => $lat,
                    'lon'       => $lon,
                    'year'      => $point->year,
                    'count'     => 0,
                ];
            }
            $grouped[$key]['count'] += $point->delta;
        }

        $this->repository->upsertBatch(array_values($grouped));
    }
}
