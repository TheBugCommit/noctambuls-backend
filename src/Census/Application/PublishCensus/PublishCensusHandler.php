<?php

declare(strict_types=1);

namespace App\Census\Application\PublishCensus;

use App\Census\Domain\Event\CensusPublishedEvent;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

readonly class PublishCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface   $censusRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface    $eventDispatcher,
    ) {
    }

    /** @throws Throwable */
    public function handle(PublishCensusRequest $request): void
    {
        $census = $this->censusRepository->findById(new CensusId($request->id));

        if ($census === null) {
            throw new CensusNotFoundException();
        }

        $census->publish();

        $this->transactionManager->begin();
        try {
            $this->censusRepository->save($census);
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->eventDispatcher->dispatch(new CensusPublishedEvent($census->id()->value()));
    }
}
