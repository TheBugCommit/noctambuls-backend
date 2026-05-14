<?php

declare(strict_types=1);

namespace App\Census\Application\GetPublishedCensuses;

use App\Census\Application\Mapper\CensusResponseMapper;
use App\Census\Domain\Repository\CensusRepositoryInterface;

final readonly class GetPublishedCensusesHandler
{
    public function __construct(private CensusRepositoryInterface $repository) {}

    public function handle(GetPublishedCensusesRequest $request): GetPublishedCensusesResponse
    {
        $censuses = $this->repository->findPublishedUpcoming($request->since);

        return CensusResponseMapper::toPublishedCensusesResponse(
            $censuses,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format(\DateTimeInterface::ATOM),
        );
    }
}
