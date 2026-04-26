<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\UpdateBird;

use App\Shared\Application\DTO\UploadFileRequest;

readonly class UpdateBirdRequest
{
    /**
     * @param array<array{languageCode: string, commonName: string, description: string}> $translations
     */
    public function __construct(
        public string $id,
        public string $scientificName,
        public string $informationUrl,
        public array  $translations,
        public ?UploadFileRequest $image,
        public ?UploadFileRequest $audio,
    ) {
    }
}
