<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Analytics;

use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ForceUpdateAnalyticsController extends AbstractController
{
    public function __construct(
        private readonly UpdateAnalyticsHandler $handler,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('analytics_force_update', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'analytics.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_analytics_dashboard');
        }

        try {
            $this->handler->handle();
            $this->addFlash('success', 'analytics.flash.updated');
        } catch (\Throwable) {
            $this->addFlash('error', 'analytics.error.update_failed');
        }

        return $this->redirectToRoute('backoffice_analytics_dashboard');
    }
}
