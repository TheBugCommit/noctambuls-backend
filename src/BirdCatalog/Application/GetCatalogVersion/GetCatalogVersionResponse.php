<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetCatalogVersion;

readonly class GetCatalogVersionResponse
{
    public function __construct(
        public int    $version,
        public string $lastUpdatedAt,
    ) {
    }
}
