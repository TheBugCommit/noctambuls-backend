<?php

declare(strict_types=1);

namespace App\Observation\Domain\Repository;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Filter\SessionFilter;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\SessionId;
use App\Profile\Domain\ValueObject\OrnithologistCode;

interface SessionRepositoryInterface
{
    public function findById(SessionId $id): ?Session;

    /** @param string[] $sessionIds @return Session[] */
    public function findByIds(array $sessionIds): array;

    /** @return string[] */
    public function findPaginatedIdsByAnnotatorId(AuthUserId $annotatorId, int $offset, int $limit, ?\DateTimeImmutable $since = null): array;

    /**
     * * @param string[] $censusIds
     * @return Session[]
     */
    public function findByCensusIdsAndAnnotator(array $censusIds, AuthUserId $annotatorId): array;

    public function countByAnnotatorId(AuthUserId $annotatorId, ?\DateTimeImmutable $since = null): int;

    public function countByParticipantCode(OrnithologistCode $code, ?\DateTimeImmutable $since = null): int;

    public function findPaginatedIdsByParticipantCode(OrnithologistCode $code, int $offset, int $limit, ?\DateTimeImmutable $since = null): array;
    public function findByIdsWithRelations(array $ids): array;

    public function findPaginatedIdsForBackoffice(SessionFilter $filter, int $page, int $limit): array;

    public function countForBackoffice(SessionFilter $filter): int;

    /** @return string[] */
    public function findAllIdsForBackoffice(SessionFilter $filter): array;

    /** @return array<array{id: string}> */
    public function findPaginatedDeletedIds(int $page, int $limit): array;

    public function countDeleted(): int;

    public function purgeDeleted(): int;

    public function purgeDeletedBefore(\DateTimeImmutable $cutoff): int;

    public function findByObservationId(ObservationId $id): ?Session;

    public function save(Session $session): void;

    public function free(Session $session): void;
}
