<?php
declare(strict_types=1);

namespace App\BirdCatalog\Application\Service;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class BirdCatalogUpdaterService
{
    public function __construct(
        private BirdRepositoryInterface $birdRepository,
        private BirdCatalogVersionRepositoryInterface $versionRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function save(Bird $bird): void
    {
        $catalogVersion = $this->versionRepository->get();

        $this->transactionManager->begin();
        try {
            $this->birdRepository->save($bird);

            $catalogVersion->increment();
            $this->versionRepository->save($catalogVersion);

            $this->transactionManager->commit();
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }

    /**
     * @throws Throwable
     */
    public function delete(Bird $bird): void
    {
        $catalogVersion = $this->versionRepository->get();

        $this->transactionManager->begin();
        try {
            $this->birdRepository->delete($bird);

            $catalogVersion->increment();
            $this->versionRepository->save($catalogVersion);

            $this->transactionManager->commit();
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
