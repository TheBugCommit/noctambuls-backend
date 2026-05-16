<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;

final readonly class AnalyticsPurgeAdjustmentListener
{
    public function __construct(
        private CronStateRepositoryInterface        $cronStateRepository,
        private AnalyticsSessionQueryInterface      $sessionQuery,
        private MapBubbleSummaryRepositoryInterface $mapBubbleRepository,
        private int                                 $precision,
    ) {}

    public function __invoke(BeforeSessionsPurgedEvent $event): void
    {
        $cronState = $this->cronStateRepository->findByJobName('update_analytics');
        if ($cronState === null) {
            return;
        }

        $dataPoints = $this->sessionQuery->findDeletedDataPoints($cronState->lastProcessedAt());

        $grouped = [];
        foreach ($dataPoints as $point) {
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

        $this->mapBubbleRepository->upsertBatch(array_values($grouped));
    }
}
