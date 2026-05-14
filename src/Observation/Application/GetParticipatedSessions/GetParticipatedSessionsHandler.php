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
        $serverTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format(\DateTimeInterface::ATOM);

        $total = $this->sessionRepository->countByParticipantCode($code, $request->since);

        $ids = $total > 0
            ? $this->sessionRepository->findPaginatedIdsByParticipantCode(
                $code,
                $request->offset,
                $request->limit,
                $request->since,
            )
            : [];

        $sessions = !empty($ids) ? $this->sessionRepository->findByIdsWithRelations($ids) : [];

        return new GetParticipatedSessionsResponse(
            sessions: ObservationResponseMapper::toNestedSessionArray($sessions),
            meta: ObservationResponseMapper::toMeta($total, $request->offset, $request->limit),
            serverTime: $serverTime,
        );
    }
}
