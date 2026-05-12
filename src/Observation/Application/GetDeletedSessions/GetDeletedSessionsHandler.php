<?php

declare(strict_types=1);

namespace App\Observation\Application\GetDeletedSessions;

use App\Observation\Application\Mapper\BackofficeSessionMapper;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Repository\SessionRepositoryInterface;

final readonly class GetDeletedSessionsHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private SessionHydrator $sessionHydrator,
    ) {
    }

    public function handle(GetDeletedSessionsRequest $request): GetDeletedSessionsResponse
    {
        $total = $this->sessionRepository->countDeleted();

        $ids = $total > 0
            ? array_column($this->sessionRepository->findPaginatedDeletedIds($request->page, $request->limit), 'id')
            : [];

        $sessions = !empty($ids) ? $this->sessionRepository->findByIdsWithRelations($ids) : [];

        ['profileMap' => $profileMap, 'censusMap' => $censusMap] = $this->sessionHydrator->hydrate($sessions);

        $result = BackofficeSessionMapper::toDeletedListArray($sessions, $profileMap, $censusMap);

        return new GetDeletedSessionsResponse(
            sessions: $result,
            total: $total,
            page: $request->page,
            limit: $request->limit,
        );
    }
}
