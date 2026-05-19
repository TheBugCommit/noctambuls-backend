<?php

declare(strict_types=1);

namespace App\Observation\Application\RestoreObservation;

use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class RestoreObservationHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(RestoreObservationRequest $request): void
    {
        $obsId = new ObservationId($request->observationId);
        $session = $this->sessionRepository->findByObservationId($obsId);

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $observation = $session->findObservationById($obsId);

        if ($observation === null) {
            throw new ObservationNotFoundException();
        }

        $this->transactionManager->begin();
        try {
            $observation->restore(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $this->sessionRepository->save($session);
            $this->transactionManager->commit();
        } catch (\DomainException|\InvalidArgumentException|\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
