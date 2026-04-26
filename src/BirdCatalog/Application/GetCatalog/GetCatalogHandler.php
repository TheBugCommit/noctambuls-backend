<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetCatalog;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;

final readonly class GetCatalogHandler
{
    public function __construct(
        private BirdRepositoryInterface $birdRepository,
    ) {
    }

    public function handle(): GetCatalogResponse
    {
        $birds = $this->birdRepository->findAll();

        $items = [];
        foreach ($birds as $bird) {
            $translations = [];
            foreach ($bird->translations() as $translation) {
                $translations[] = [
                    'languageCode' => $translation->languageCode()->value,
                    'commonName'   => $translation->commonName()->value(),
                    'description'  => $translation->description()->value(),
                ];
            }

            $items[] = [
                'id'             => $bird->id()->value(),
                'scientificName' => $bird->scientificName()->value(),
                'imagePath'      => $bird->imagePath()->value(),
                'audioPath'      => $bird->audioPath()->value(),
                'informationUrl' => $bird->informationUrl()->value(),
                'translations'   => $translations,
            ];
        }

        return new GetCatalogResponse($items);
    }
}
