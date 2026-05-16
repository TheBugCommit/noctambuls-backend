<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Analytics;

use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsHandler;
use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class UpdateAnalyticsController
{
    public function __construct(
        private UpdateAnalyticsHandler $handler,
        private string $cronSecretToken,
    ) {}

    public function __invoke(Request $request): UpdateAnalyticsResponse
    {
        $token = $request->headers->get('X-Cron-Token', '');

        if ($token !== $this->cronSecretToken) {
            throw new UnauthorizedHttpException('Token', 'Invalid or missing cron token');
        }

        return $this->handler->handle();
    }
}
