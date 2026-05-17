<?php

declare(strict_types=1);

namespace App\Census\Application\DeleteCensus;

use App\Census\Domain\Event\CensusCancelledEvent;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

readonly class DeleteCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface   $censusRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface    $eventDispatcher,
    ) {
    }

    /** @throws Throwable */
    public function handle(DeleteCensusRequest $request): void
    {
        $census = $this->censusRepository->findById(new CensusId($request->id));

        if ($census === null) {
            throw new CensusNotFoundException();
        }

        $isSoftDelete = $census->status()->isPublished() && !$census->isWithinGracePeriod();

        $this->transactionManager->begin();
        try {
            if ($isSoftDelete) {
                $census->softDelete();
                $this->censusRepository->save($census);
            } else {
                $this->censusRepository->delete($census);
            }
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        if ($isSoftDelete) {
            $this->eventDispatcher->dispatch(new CensusCancelledEvent($census->id()->value()));
        }
    }
}
