<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\CreateBird;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\Shared\Application\DTO\UploadFileRequest;

readonly class CreateBirdRequest
{
    /**
     * @param BirdTranslationRequest[] $translations
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
