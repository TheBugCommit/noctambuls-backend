<?php
declare(strict_types=1);

namespace App\BirdCatalog\Application\Mapper;

use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionResponse;
use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;

final class CatalogVersionResponseMapper
{
    public static function toCatalogVersionResponse(BirdCatalogVersion $catalogVersion): GetCatalogVersionResponse
    {
        return new GetCatalogVersionResponse(
            $catalogVersion->version(),
            $catalogVersion->lastUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
