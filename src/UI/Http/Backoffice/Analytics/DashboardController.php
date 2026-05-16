<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Analytics;

use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly CronStateRepositoryInterface $cronStateRepository,
    ) {}

    public function __invoke(): Response
    {
        $cronState = $this->cronStateRepository->findByJobName(CronJobName::UpdateAnalytics);

        return $this->render('backoffice/analytics/dashboard.html.twig', [
            'lastProcessedAt' => $cronState?->lastProcessedAt(),
        ]);
    }
}
