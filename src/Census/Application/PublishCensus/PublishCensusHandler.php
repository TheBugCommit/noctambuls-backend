<?php

declare(strict_types=1);

namespace App\Census\Application\PublishCensus;

use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class PublishCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface   $censusRepository,
        private TransactionManagerInterface $transactionManager,
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
    }
}
