<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\DeleteBird;

use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class DeleteBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface               $birdRepository,
        private BirdCatalogVersionRepositoryInterface $versionRepository,
        private TransactionManagerInterface           $transactionManager,
        private FileRemoverInterface                  $fileRemover,
    ) {
    }

    /** @throws Throwable */
    public function handle(DeleteBirdRequest $request): void
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $catalogVersion = $this->versionRepository->get();

        $this->transactionManager->begin();
        try {
            $this->birdRepository->delete($bird);
            $catalogVersion->increment();
            $this->versionRepository->save($catalogVersion);
            $this->fileRemover->remove($bird->imagePath());
            $this->fileRemover->remove($bird->audioPath());
            $this->transactionManager->commit();
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
