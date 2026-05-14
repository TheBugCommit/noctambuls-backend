<?php

declare(strict_types=1);

namespace App\Census\Domain\Repository;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusStatus;

interface CensusRepositoryInterface
{
    public function findById(CensusId $id): ?Census;
    public function findByIds(array $censusIds): array;

    /** @return Census[] */
    public function findPaginated(int $page, int $limit): array;

    /**
     * @param string[] $censusIds
     * @return string[]
     */
    public function findCensusIdsByStatus(array $censusIds, CensusStatus $status): array;

    /** @return Census[] */
    public function findPublishedUpcoming(?\DateTimeImmutable $since = null): array;

    public function count(): int;

    public function save(Census $census): void;

    public function delete(Census $census): void;
}
