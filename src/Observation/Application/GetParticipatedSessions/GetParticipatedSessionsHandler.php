<?php

declare(strict_types=1);

namespace App\Observation\Application\GetParticipatedSessions;

use App\Observation\Application\Mapper\ObservationResponseMapper;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;

final readonly class GetParticipatedSessionsHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
    ) {
    }

    public function handle(GetParticipatedSessionsRequest $request): GetParticipatedSessionsResponse
    {
        $code = new OrnithologistCode($request->ornithologistCode);

        $total = $this->sessionRepository->countByParticipantCode($code);

        $ids = $total > 0
            ? $this->sessionRepository->findPaginatedIdsByParticipantCode(
                $code,
                $request->offset,
                $request->limit
            )
            : [];

        $ids = array_column($ids, 'id');

        $sessions = !empty($ids) ? $this->sessionRepository->findByIdsWithRelations($ids) : [];

        return new GetParticipatedSessionsResponse(
            sessions: ObservationResponseMapper::toNestedSessionArray($sessions),
            meta: ObservationResponseMapper::toMeta($total, $request->offset, $request->limit),
        );
    }
}
