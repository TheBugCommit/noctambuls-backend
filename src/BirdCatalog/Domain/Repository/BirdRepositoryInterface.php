<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Repository;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\ValueObject\BirdId;

interface BirdRepositoryInterface
{
    /** @return Bird[] */
    public function findAll(): array;

    public function findById(BirdId $id): ?Bird;

    public function save(Bird $bird): void;

    public function delete(Bird $bird): void;
}
