<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;

final readonly class MapBubbleSummaryListener
{
    public function __construct(
        private MapBubbleSummaryRepositoryInterface $repository,
        private int                                 $precision,
        private GroupDataPointsService              $groupDataPointsService,
    )
    {
    }

    public function __invoke(AnalyticsDataCollectedEvent $event): void
    {
        $grouped = $this->groupDataPointsService->groupBubbleDataPoints($event->dataPoints(), $this->precision);
        $this->repository->upsertBatch(array_values($grouped));
    }
}
