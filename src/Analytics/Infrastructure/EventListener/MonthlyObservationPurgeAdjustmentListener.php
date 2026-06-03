<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;

final readonly class MonthlyObservationPurgeAdjustmentListener
{
    public function __construct(
        private CronStateRepositoryInterface                 $cronStateRepository,
        private AnalyticsSessionQueryInterface               $sessionQuery,
        private MonthlyObservationSummaryRepositoryInterface $monthlyRepository,
        private GroupDataPointsService                       $groupDataPointsService,
    )
    {
    }

    public function __invoke(BeforeSessionsPurgedEvent $event): void
    {
        $cronState = $this->cronStateRepository->findByJobName(CronJobName::UpdateAnalytics);
        if ($cronState === null) {
            return;
        }

        $dataPoints = $this->sessionQuery->findDeletedDataPoints($cronState->lastProcessedAt(), $event->cutoff);

        $grouped = $this->groupDataPointsService->groupMonthlyDataPoints($dataPoints);

        $rows = array_values(array_filter($grouped, static fn(array $r) => $r['count'] !== 0));
        $this->monthlyRepository->upsertBatch($rows);
    }
}
