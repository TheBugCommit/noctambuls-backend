<?php
declare(strict_types=1);

namespace App\BirdCatalog\Application\Mapper;

use App\BirdCatalog\Application\CreateBird\CreateBirdResponse;
use App\BirdCatalog\Application\GetBird\GetBirdResponse;
use App\BirdCatalog\Application\GetCatalog\GetCatalogResponse;
use App\BirdCatalog\Application\ListBirds\ListBirdsResponse;
use App\BirdCatalog\Domain\Entity\Bird;

final class BirdResponseMapper
{
    public static function toGetBirdResponse(Bird $bird): GetBirdResponse
    {
        return new GetBirdResponse(
            $bird->id()->value(),
            $bird->scientificName()->value(),
            $bird->imagePath()->value(),
            $bird->audioPath()->value(),
            $bird->informationUrl()->value(),
            self::toTranslationsArray($bird)
        );
    }

    public static function toCreateBirdResponse(Bird $bird): CreateBirdResponse
    {
        return new CreateBirdResponse($bird->id()->value());
    }

    public static function toGetCatalogResponse(Bird $birds): GetCatalogResponse
    {
        $items = [];
        foreach ($birds as $bird) {
            $items[] = self::toCatalogArray($bird);
        }
        return new GetCatalogResponse($items);
    }

    public static function toGetListBirds(Bird $birds): ListBirdsResponse
    {
        $items = [];
        foreach ($birds as $bird) {
            $items[] = self::toCatalogArray($bird);
        }
        return new ListBirdsResponse($items);
    }

    private static function toCatalogArray(Bird $bird): array
    {
        return [
            'id'             => $bird->id()->value(),
            'scientificName' => $bird->scientificName()->value(),
            'imagePath'      => $bird->imagePath()->value(),
            'audioPath'      => $bird->audioPath()->value(),
            'informationUrl' => $bird->informationUrl()->value(),
            'translations'   => self::toTranslationsArray($bird),
        ];
    }

    private static function toTranslationsArray(Bird $bird): array
    {
        $translations = [];
        foreach ($bird->translations() as $translation) {
            $translations[] = [
                'languageCode' => $translation->languageCode()->value,
                'commonName'   => $translation->commonName()->value(),
                'description'  => $translation->description()->value(),
            ];
        }

        return $translations;
    }
}
