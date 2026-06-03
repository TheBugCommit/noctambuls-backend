<?php

declare(strict_types=1);

namespace App\Census\Application\DeleteCensus;

use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Exception\CensusNotEditableException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

readonly class DeleteCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface   $censusRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /** @throws Throwable */
    public function handle(DeleteCensusRequest $request): void
    {
        $census = $this->censusRepository->findById(new CensusId($request->id));

        if ($census === null) {
            throw new CensusNotFoundException();
        }

        if ($census->status()->isPublished() && !$census->isWithinGracePeriod()) {
            throw new CensusNotEditableException();
        }

        $this->transactionManager->begin();
        try {
            $this->censusRepository->delete($census);
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
