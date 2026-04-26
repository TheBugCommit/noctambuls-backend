<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Repository;

use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;

interface BirdCatalogVersionRepositoryInterface
{
    public function get(): BirdCatalogVersion;

    public function save(BirdCatalogVersion $version): void;
}
