<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetBird;

readonly class GetBirdResponse
{
    /**
     * @param array<array{id: string, languageCode: string, commonName: string, description: string}> $translations
     */
    public function __construct(
        public string $id,
        public string $scientificName,
        public string $imagePath,
        public string $audioPath,
        public string $informationUrl,
        public array  $translations,
    ) {
    }
}
