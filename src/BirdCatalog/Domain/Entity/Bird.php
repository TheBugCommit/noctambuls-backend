<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Entity;

use App\BirdCatalog\Domain\Exception\AlreadyExistsTranslationException;
use App\BirdCatalog\Domain\Exception\BirdMustHaveAllTranslations;
use App\BirdCatalog\Domain\Exception\TranslationNotFoundException;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Bird
{
    /** @var Collection<int, BirdTranslation> */
    private Collection $translations;

    public function __construct(
        private readonly BirdId        $id,
        private ScientificName         $scientificName,
        private ImagePath              $imagePath,
        private AudioPath              $audioPath,
        private InformationUrl         $informationUrl,
    ) {
        $this->translations = new ArrayCollection();
    }

    public static function create(
        BirdId         $id,
        ScientificName $scientificName,
        ImagePath      $imagePath,
        AudioPath      $audioPath,
        InformationUrl $informationUrl,
    ): self {
        return new self($id, $scientificName, $imagePath, $audioPath, $informationUrl);
    }

    public function update(
        ScientificName $scientificName,
        ImagePath      $imagePath,
        AudioPath      $audioPath,
        InformationUrl $informationUrl,
    ): void {
        $this->scientificName = $scientificName;
        $this->imagePath = $imagePath;
        $this->audioPath = $audioPath;
        $this->informationUrl = $informationUrl;
    }

    public function addTranslation(BirdTranslation $translation): void
    {
        foreach ($this->translations as $existing) {
            if ($existing->languageCode() === $translation->languageCode()) {
                throw new AlreadyExistsTranslationException();
            }
        }
        $this->translations->add($translation);
    }

    public function updateTranslation(LanguageCode $languageCode, CommonName $commonName, BirdDescription $description): void
    {
        foreach ($this->translations as $translation) {
            if ($translation->languageCode() === $languageCode) {
                $translation->update($commonName, $description);
                return;
            }
        }
        throw new TranslationNotFoundException();
    }

    public function assertHasRequiredTranslations(): void
    {
        $currentLanguages = array_map(
            fn(BirdTranslation $t) => $t->languageCode(),
            $this->translations->toArray()
        );

        foreach (LanguageCode::cases() as $requiredLang) {
            if (!in_array($requiredLang, $currentLanguages, true)) {
                throw new BirdMustHaveAllTranslations();
            }
        }
    }

    public function id(): BirdId
    {
        return $this->id;
    }

    public function scientificName(): ScientificName
    {
        return $this->scientificName;
    }

    public function imagePath(): ImagePath
    {
        return $this->imagePath;
    }

    public function audioPath(): AudioPath
    {
        return $this->audioPath;
    }

    public function informationUrl(): InformationUrl
    {
        return $this->informationUrl;
    }

    /** @return Collection<int, BirdTranslation> */
    public function translations(): Collection
    {
        return $this->translations;
    }
}
