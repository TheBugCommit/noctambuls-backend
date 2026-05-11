<?php

declare(strict_types=1);

namespace App\Observation\Domain\Repository;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\ValueObject\SessionId;
use App\Profile\Domain\ValueObject\OrnithologistCode;

interface SessionRepositoryInterface
{
    public function findById(SessionId $id): ?Session;

    /** @param string[] $sessionIds @return Session[] */
    public function findByIds(array $sessionIds): array;

    /** @return string[] */
    public function findPaginatedIdsByAnnotatorId(AuthUserId $annotatorId, int $offset, int $limit): array;

    /**
     * * @param string[] $censusIds
     * @return Session[]
     */
    public function findByCensusIdsAndAnnotator(array $censusIds, AuthUserId $annotatorId): array;

    public function countByAnnotatorId(AuthUserId $annotatorId): int;

    public function countByParticipantCode(OrnithologistCode $code): int;

    public function findPaginatedIdsByParticipantCode(OrnithologistCode $code, int $offset, int $limit): array;
    public function findByIdsWithRelations(array $ids): array;

    public function save(Session $session): void;

    public function free(Session $session): void;
}
