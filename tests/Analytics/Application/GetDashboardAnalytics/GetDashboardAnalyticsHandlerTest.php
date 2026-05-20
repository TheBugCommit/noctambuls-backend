<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Application\GetDashboardAnalytics;

use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsHandler;
use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsRequest;
use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetDashboardAnalyticsHandlerTest extends TestCase
{
    private CronStateRepositoryInterface&MockObject $cronStateRepository;
    private MonthlyObservationSummaryRepositoryInterface&MockObject $monthlyRepository;
    private GetDashboardAnalyticsHandler $handler;

    protected function setUp(): void
    {
        $this->cronStateRepository = $this->createMock(CronStateRepositoryInterface::class);
        $this->monthlyRepository   = $this->createMock(MonthlyObservationSummaryRepositoryInterface::class);
        $this->handler             = new GetDashboardAnalyticsHandler(
            $this->cronStateRepository,
            $this->monthlyRepository
        );
    }

    public function test_passes_requested_year_to_monthly_repository(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->monthlyRepository->method('findAvailableYears')->willReturn([2025]);
        $this->monthlyRepository->expects(self::once())
            ->method('findByYear')
            ->with(2025)
            ->willReturn([]);

        $this->handler->execute(new GetDashboardAnalyticsRequest(2025));
    }

    public function test_uses_current_year_when_no_available_years(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->monthlyRepository->method('findAvailableYears')->willReturn([]);
        $this->monthlyRepository->method('findByYear')->willReturn([]);

        $response = $this->handler->execute(new GetDashboardAnalyticsRequest((int) date('Y')));

        self::assertContains((int) date('Y'), $response->availableYears);
    }

    public function test_returns_available_years_from_repository_when_non_empty(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->monthlyRepository->method('findAvailableYears')->willReturn([2023, 2024, 2025]);
        $this->monthlyRepository->method('findByYear')->willReturn([]);

        $response = $this->handler->execute(new GetDashboardAnalyticsRequest(2025));

        self::assertSame([2023, 2024, 2025], $response->availableYears);
    }

    public function test_passes_null_last_processed_at_when_no_cron_state(): void
    {
        $this->cronStateRepository->method('findByJobName')->willReturn(null);
        $this->monthlyRepository->method('findAvailableYears')->willReturn([2025]);
        $this->monthlyRepository->method('findByYear')->willReturn([]);

        $response = $this->handler->execute(new GetDashboardAnalyticsRequest(2025));

        self::assertNull($response->lastProcessedAt);
    }

    public function test_passes_cron_state_last_processed_at_when_available(): void
    {
        $lastProcessedAt = new \DateTimeImmutable('2025-01-15T10:00:00+00:00');
        $cronState = new CronState(CronJobName::UpdateAnalytics, $lastProcessedAt);

        $this->cronStateRepository->method('findByJobName')->willReturn($cronState);
        $this->monthlyRepository->method('findAvailableYears')->willReturn([2025]);
        $this->monthlyRepository->method('findByYear')->willReturn([]);

        $response = $this->handler->execute(new GetDashboardAnalyticsRequest(2025));

        self::assertSame($lastProcessedAt, $response->lastProcessedAt);
    }
}
