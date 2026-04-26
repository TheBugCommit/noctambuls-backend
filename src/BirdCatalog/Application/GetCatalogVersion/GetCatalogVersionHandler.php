<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetCatalogVersion;

use App\BirdCatalog\Application\Mapper\CatalogVersionResponseMapper;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;

final readonly class GetCatalogVersionHandler
{
    public function __construct(
        private BirdCatalogVersionRepositoryInterface $versionRepository,
    ) {
    }

    public function handle(): GetCatalogVersionResponse
    {
        $version = $this->versionRepository->get();
        return CatalogVersionResponseMapper::toCatalogVersionResponse($version);
    }
}
