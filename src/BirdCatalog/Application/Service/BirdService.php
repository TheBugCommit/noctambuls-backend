<?php

namespace App\BirdCatalog\Application\Service;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;

final readonly class BirdService
{
    public function __construct(
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    /**
     * @param BirdTranslationRequest[] $translations
     */
    public function create(
        string $scientificName,
        ImagePath $imagePath,
        AudioPath $audioPath,
        string $informationUrl,
        array $translations
    ): Bird {
        $bird = Bird::create(
            new BirdId($this->uuidGenerator->generate()),
            new ScientificName($scientificName),
            $imagePath,
            $audioPath,
            new InformationUrl($informationUrl),
        );

        foreach ($translations as $translationInput) {
            $bird->addTranslation(new BirdTranslation(
                new BirdTranslationId($this->uuidGenerator->generate()),
                $bird,
                $translationInput->languageCode,
                new CommonName($translationInput->commonName),
                new BirdDescription($translationInput->description),
            ));
        }

        $bird->assertHasRequiredTranslations();

        return $bird;
    }
}
