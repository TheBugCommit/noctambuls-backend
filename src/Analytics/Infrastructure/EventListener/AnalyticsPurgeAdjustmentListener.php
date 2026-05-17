<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\EventListener;

use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use App\Analytics\Infrastructure\Service\GroupDataPointsService;
use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;

final readonly class AnalyticsPurgeAdjustmentListener
{
    public function __construct(
        private CronStateRepositoryInterface        $cronStateRepository,
        private AnalyticsSessionQueryInterface      $sessionQuery,
        private MapBubbleSummaryRepositoryInterface $mapBubbleRepository,
        private int                                 $precision,
        private GroupDataPointsService              $groupDataPointsService,
    )
    {
    }

    public function __invoke(BeforeSessionsPurgedEvent $event): void
    {
        $cronState = $this->cronStateRepository->findByJobName(CronJobName::UpdateAnalytics);
        if ($cronState === null) {
            return;
        }

        $dataPoints = $this->sessionQuery->findDeletedDataPoints($cronState->lastProcessedAt());

        $grouped = $this->groupDataPointsService->groupBubbleDataPoints($dataPoints, $this->precision);

        $this->mapBubbleRepository->upsertBatch(array_values($grouped));
    }
}
