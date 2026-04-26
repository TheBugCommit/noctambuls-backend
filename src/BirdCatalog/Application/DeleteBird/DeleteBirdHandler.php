<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\DeleteBird;

use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use Throwable;

final readonly class DeleteBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface   $birdRepository,
        private BirdCatalogUpdaterService $birdCatalogUpdater,
    ) {
    }

    /** @throws Throwable */
    public function handle(DeleteBirdRequest $request): void
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $this->birdCatalogUpdater->delete($bird);
    }
}
