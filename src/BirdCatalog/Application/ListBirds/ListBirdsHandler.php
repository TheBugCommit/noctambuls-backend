<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\ListBirds;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;

final readonly class ListBirdsHandler
{
    public function __construct(
        private BirdRepositoryInterface $birdRepository,
    ) {
    }

    public function handle(): ListBirdsResponse
    {
        $birds = $this->birdRepository->findAll();

        $items = [];
        foreach ($birds as $bird) {
            $translations = [];
            foreach ($bird->translations() as $translation) {
                $translations[] = [
                    'languageCode' => $translation->languageCode()->value,
                    'commonName'   => $translation->commonName()->value(),
                ];
            }

            $items[] = [
                'id'             => $bird->id()->value(),
                'scientificName' => $bird->scientificName()->value(),
                'translations'   => $translations,
            ];
        }

        return new ListBirdsResponse($items);
    }
}
