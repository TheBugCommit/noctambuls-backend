<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetBird;

use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;

final readonly class GetBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface $birdRepository,
    ) {
    }

    public function handle(GetBirdRequest $request): GetBirdResponse
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $translations = [];
        foreach ($bird->translations() as $translation) {
            $translations[] = [
                'id'           => $translation->id()->value(),
                'languageCode' => $translation->languageCode()->value,
                'commonName'   => $translation->commonName()->value(),
                'description'  => $translation->description()->value(),
            ];
        }

        return new GetBirdResponse(
            $bird->id()->value(),
            $bird->scientificName()->value(),
            $bird->imagePath()->value(),
            $bird->audioPath()->value(),
            $bird->informationUrl()->value(),
            $translations,
        );
    }
}
