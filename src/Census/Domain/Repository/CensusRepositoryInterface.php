<?php

declare(strict_types=1);

namespace App\Census\Domain\Repository;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;

interface CensusRepositoryInterface
{
    public function findById(CensusId $id): ?Census;

    /** @return Census[] */
    public function findPaginated(int $page, int $limit): array;

    public function count(): int;

    public function save(Census $census): void;

    public function delete(Census $census): void;
}
