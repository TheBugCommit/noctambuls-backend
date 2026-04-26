<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetCatalog;

use App\BirdCatalog\Application\Mapper\BirdResponseMapper;
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
        return BirdResponseMapper::toGetCatalogResponse($birds);
    }
}
