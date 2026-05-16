<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetMapBubbleSummary;

use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use DateTimeImmutable;
use DateTimeZone;

final readonly class GetMapBubbleSummaryHandler
{
    public function __construct(
        private MapBubbleSummaryRepositoryInterface $repository,
    ) {}

    public function handle(GetMapBubbleSummaryRequest $request): GetMapBubbleSummaryResponse
    {
        $since = null;
        if ($request->since !== null) {
            $since = new DateTimeImmutable($request->since, new DateTimeZone('UTC'));
        }

        $items = $this->repository->findSince($since);

        $serverTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format(\DateTimeInterface::ATOM);
        return new GetMapBubbleSummaryResponse(
            serverTime: $serverTime,
            items: $items,
        );
    }
}
