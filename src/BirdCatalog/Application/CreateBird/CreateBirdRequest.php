<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\CreateBird;

use App\Shared\Application\DTO\UploadFileRequest;

readonly class CreateBirdRequest
{
    /**
     * @param array<array{languageCode: string, commonName: string, description: string}> $translations
     */
    public function __construct(
        public string $scientificName,
        public UploadFileRequest $image,
        public UploadFileRequest $audio,
        public string $informationUrl,
        public array  $translations,
    ) {
    }
}
