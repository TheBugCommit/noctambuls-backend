<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\ListBirds;

use App\BirdCatalog\Application\Mapper\BirdResponseMapper;
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
        return BirdResponseMapper::toGetListBirds($birds);
    }
}
