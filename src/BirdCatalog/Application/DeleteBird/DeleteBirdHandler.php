<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\DeleteBird;

use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Shared\Application\Service\FileRemoverInterface;
use Throwable;

final readonly class DeleteBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface   $birdRepository,
        private BirdCatalogUpdaterService $birdCatalogUpdater,
        private FileRemoverInterface      $fileRemover,
    ) {
    }

    /** @throws Throwable */
    public function handle(DeleteBirdRequest $request): void
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $imagePath = $bird->imagePath();
        $audioPath = $bird->audioPath();

        $this->birdCatalogUpdater->delete($bird);

        if ($imagePath !== null && $imagePath !== '') {
            $this->fileRemover->remove($imagePath);
        }
        if ($audioPath !== null && $audioPath !== '') {
            $this->fileRemover->remove($audioPath);
        }
    }
}
