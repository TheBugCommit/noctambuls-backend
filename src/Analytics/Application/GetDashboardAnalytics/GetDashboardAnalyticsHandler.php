<?php
declare(strict_types=1);

namespace App\Analytics\Application\GetDashboardAnalytics;

use App\Analytics\Application\Mapper\DashboardResponseMapper;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;

final readonly class GetDashboardAnalyticsHandler
{
    public function __construct(
        private CronStateRepositoryInterface                 $cronStateRepository,
        private MonthlyObservationSummaryRepositoryInterface $monthlyRepository,
    ) {}

    public function execute(GetDashboardAnalyticsRequest $request): GetDashboardAnalyticsResponse
    {
        $cronState = $this->cronStateRepository->findByJobName(CronJobName::UpdateAnalytics);

        $availableYears = $this->monthlyRepository->findAvailableYears();
        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        $rows = $this->monthlyRepository->findByYear($request->requestedYear);

        return DashboardResponseMapper::toDashboardResponse(
            $cronState->lastProcessedAt(),
            $availableYears,
            $request->requestedYear,
            $rows,
        );
    }
}
