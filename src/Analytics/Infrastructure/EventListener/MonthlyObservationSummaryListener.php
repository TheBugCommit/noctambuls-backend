<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;

final readonly class MonthlyObservationSummaryListener
{
    public function __construct(
        private MonthlyObservationSummaryRepositoryInterface $repository,
        private GroupDataPointsService                       $groupDataPointsService,
    )
    {
    }

    public function __invoke(AnalyticsDataCollectedEvent $event): void
    {
        $grouped = $this->groupDataPointsService->groupMonthlyDataPoints($event->dataPoints());
        $rows = array_values(array_filter($grouped, static fn(array $r) => $r['count'] !== 0));
        $this->repository->upsertBatch($rows);
    }
}
