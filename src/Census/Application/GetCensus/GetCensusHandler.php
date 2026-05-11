<?php

declare(strict_types=1);

namespace App\Census\Application\GetCensus;

use App\Census\Application\Mapper\CensusResponseMapper;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;

readonly class GetCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface $censusRepository,
    ) {
    }

    public function handle(GetCensusRequest $request): GetCensusResponse
    {
        $census = $this->censusRepository->findById(new CensusId($request->id));

        if ($census === null) {
            throw new CensusNotFoundException();
        }

        return CensusResponseMapper::toGetCensusResponse($census);
    }
}
