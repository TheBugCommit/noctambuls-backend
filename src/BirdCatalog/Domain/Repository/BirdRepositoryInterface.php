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

    /**
     * @param string[] $birdIds
     * @return string[]
     */
    public function findValidBirdIds(array $birdIds): array;

    public function save(Bird $bird): void;

    public function delete(Bird $bird): void;
}
