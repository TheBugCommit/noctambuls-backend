<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Entity;

use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;

class BirdTranslation
{
    public function __construct(
        private readonly BirdTranslationId $id,
        private readonly Bird              $bird,
        private readonly LanguageCode      $languageCode,
        private CommonName                 $commonName,
        private BirdDescription            $description,
    ) {
    }

    public function create(
        CommonName $commonName,
        BirdDescription $description
    ): void
    {
        $this->commonName = $commonName;
        $this->description = $description;
    }

    public function id(): BirdTranslationId
    {
        return $this->id;
    }

    public function bird(): Bird
    {
        return $this->bird;
    }

    public function languageCode(): LanguageCode
    {
        return $this->languageCode;
    }

    public function commonName(): CommonName
    {
        return $this->commonName;
    }

    public function description(): BirdDescription
    {
        return $this->description;
    }

    public function update(CommonName $commonName, BirdDescription $description): void
    {
        $this->commonName = $commonName;
        $this->description = $description;
    }

    public function equals(self $other): bool
    {
        return $this->id->value() === $other->id->value();
    }
}
