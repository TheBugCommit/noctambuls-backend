<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForBackoffice;

use App\Observation\Application\Mapper\BackofficeSessionMapper;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Filter\SessionFilter;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\SessionType;

final readonly class GetSessionsForBackofficeHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private SessionHydrator $sessionHydrator,
    ) {
    }

    public function handle(GetSessionsForBackofficeRequest $request): GetSessionsForBackofficeResponse
    {
        $filter = new SessionFilter(
            sessionId: $request->sessionId,
            sessionType: $request->sessionType !== null ? SessionType::from((int)$request->sessionType) : null,
            dateFrom: $request->dateFrom,
            dateTo: $request->dateTo,
            hasObservations: $request->hasObservations,
            censusId: $request->censusId,
            isDeleted: false,
            ornithologistCode: $request->ornithologistCode,
            listeningPoint: $request->listeningPoint,
            censusTitle: $request->censusTitle,
        );

        $total = $this->sessionRepository->countForBackoffice($filter);

        $ids = $total > 0
            ? $this->sessionRepository->findPaginatedIdsForBackoffice($filter, $request->page, $request->limit)
            : [];

        $sessions = !empty($ids) ? $this->sessionRepository->findByIdsWithRelations($ids) : [];

        ['profileMap' => $profileMap, 'censusMap' => $censusMap] = $this->sessionHydrator->hydrate($sessions);

        $result = BackofficeSessionMapper::toActiveListArray($sessions, $profileMap, $censusMap);

        return new GetSessionsForBackofficeResponse(
            sessions: $result,
            total: $total,
            page: $request->page,
            limit: $request->limit,
        );
    }
}
