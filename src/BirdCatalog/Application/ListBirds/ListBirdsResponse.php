<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\ListBirds;

readonly class ListBirdsResponse
{
    /**
     * @param array<array{id: string, scientificName: string, translations: array<array{languageCode: string, commonName: string}>}> $birds
     */
    public function __construct(
        public array $birds,
    ) {
    }
}
