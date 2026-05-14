<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Census;

use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesHandler;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesRequest;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class GetPublishedCensusesController
{
    public function __construct(private GetPublishedCensusesHandler $handler) {}

    public function __invoke(Request $request): GetPublishedCensusesResponse
    {
        $since = $this->parseSince($request->query->get('since'));

        return $this->handler->handle(new GetPublishedCensusesRequest(since: $since));
    }

    private function parseSince(?string $sinceStr): ?\DateTimeImmutable
    {
        if ($sinceStr === null) {
            return null;
        }

        try {
            return new \DateTimeImmutable($sinceStr)->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Exception) {
            throw new BadRequestHttpException('Invalid since parameter');
        }
    }
}
