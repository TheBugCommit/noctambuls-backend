<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Analytics;

use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryHandler;
use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryRequest;
use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class GetMapBubbleSummaryController
{
    public function __construct(private GetMapBubbleSummaryHandler $handler) {}

    public function __invoke(Request $request): GetMapBubbleSummaryResponse
    {
        $since = $request->query->get('since');

        if ($since !== null) {
            try {
                new \DateTimeImmutable($since, new \DateTimeZone('UTC'));
            } catch (\Throwable) {
                throw new BadRequestHttpException('Invalid since format. Expected ISO-8601 UTC (e.g. 2026-05-01T00:00:00Z).');
            }
        }

        return $this->handler->handle(new GetMapBubbleSummaryRequest(since: $since));
    }
}
