<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetCatalog;

readonly class GetCatalogResponse
{
    /**
     * @param array<array{id: string, scientificName: string, imagePath: string, audioPath: string, informationUrl: string, translations: array<array{languageCode: string, commonName: string, description: string}>}> $birds
     */
    public function __construct(
        public array $birds,
    ) {
    }
}
