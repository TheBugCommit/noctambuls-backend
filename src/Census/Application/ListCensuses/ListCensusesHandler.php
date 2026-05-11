<?php

declare(strict_types=1);

namespace App\Census\Application\ListCensuses;

use App\Census\Application\Mapper\CensusResponseMapper;
use App\Census\Domain\Repository\CensusRepositoryInterface;

readonly class ListCensusesHandler
{
    public function __construct(
        private CensusRepositoryInterface $censusRepository,
    ) {
    }

    public function handle(ListCensusesRequest $request): ListCensusesResponse
    {
        $censuses = $this->censusRepository->findPaginated($request->page, $request->limit);
        $total = $this->censusRepository->count();

        return CensusResponseMapper::toListCensusesResponse($censuses, $total);
    }
}
