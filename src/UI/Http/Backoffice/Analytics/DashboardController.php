<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Analytics;

use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsHandler;
use App\Analytics\Application\GetDashboardAnalytics\GetDashboardAnalyticsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly GetDashboardAnalyticsHandler $getDashboardAnalyticsHandler
    )
    {
    }

    public function __invoke(Request $request): Response
    {
        $currentYear = (int)date('Y');
        $selectedYear = (int)$request->query->get('year', $currentYear);

        $response = $this->getDashboardAnalyticsHandler->execute(
            new GetDashboardAnalyticsRequest($selectedYear)
        );

        return $this->render('backoffice/analytics/analytics.html.twig', [
            'lastProcessedAt' => $response->lastProcessedAt->format('c'),
            'availableYears'  => $response->availableYears,
            'selectedYear'    => $response->selectedYear,
            'chartData'       => $response->chartData,
            'percentageData'  => $response->percentageData,
        ]);
    }
}
