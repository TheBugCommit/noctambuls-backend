<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForCensus;

use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Application\Mapper\BackofficeSessionMapper;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Filter\SessionFilter;
use App\Observation\Domain\Repository\SessionRepositoryInterface;

final readonly class GetSessionsForCensusHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private SessionHydrator $sessionHydrator,
    ) {
    }

    public function handle(GetSessionsForCensusRequest $request): GetSessionsForCensusResponse
    {
        $filter = new SessionFilter(
            censusId: new CensusId($request->censusId),
            isDeleted: false,
        );

        $ids = $this->sessionRepository->findAllIdsForBackoffice($filter);
        $sessions = !empty($ids) ? $this->sessionRepository->findByIdsWithRelations($ids) : [];

        ['profileMap' => $profileMap] = $this->sessionHydrator->hydrate($sessions);

        $result = BackofficeSessionMapper::toCensusDetailArray($sessions, $profileMap);

        return new GetSessionsForCensusResponse(
            sessions: $result,
            total:    count($result),
        );
    }
}
