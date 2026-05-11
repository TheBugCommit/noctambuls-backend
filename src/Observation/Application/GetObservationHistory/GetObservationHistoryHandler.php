<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationHistory;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Application\Mapper\ObservationResponseMapper;
use App\Observation\Domain\Repository\ObservationRepositoryInterface;
use App\Observation\Domain\Repository\SessionParticipantRepositoryInterface;
use App\Observation\Domain\Repository\SessionRepositoryInterface;

final readonly class GetObservationHistoryHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
    )
    {
    }

    public function handle(GetObservationHistoryRequest $request): GetObservationHistoryResponse
    {
        $annotatorId = new AuthUserId($request->annotatorId);

        $total = $this->sessionRepository->countByAnnotatorId($annotatorId);

        $ids = $total > 0
            ? $this->sessionRepository->findPaginatedIdsByAnnotatorId($annotatorId, $request->offset, $request->limit)
            : [];

        $ids = array_column($ids, 'id');

        $sessions = !empty($ids)
            ? $this->sessionRepository->findByIdsWithRelations($ids)
            : [];

        return new GetObservationHistoryResponse(
            sessions: ObservationResponseMapper::toNestedSessionArray($sessions),
            meta: ObservationResponseMapper::toMeta($total, $request->offset, $request->limit),
        );
    }
}
