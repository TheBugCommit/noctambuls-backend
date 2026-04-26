<?php
declare(strict_types=1);

namespace App\BirdCatalog\Application\BirdTranslation;

use App\BirdCatalog\Domain\ValueObject\LanguageCode;

readonly class BirdTranslationRequest
{
    public function __construct(
        public LanguageCode $languageCode,
        public string $commonName,
        public string $description,
    ) {
    }
}
