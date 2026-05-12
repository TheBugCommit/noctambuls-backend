<?php

declare(strict_types=1);

namespace App\Observation\Application\UpdateObservation;

use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class UpdateObservationHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(UpdateObservationRequest $request): void
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
            $observation->update(
                speciesId: new BirdId($request->speciesId),
                contactType: ContactType::from($request->contactType),
                quantity: new Quantity($request->quantity),
                startTime: $observation->startTime(),
                endTime: $observation->endTime(),
                notes: $request->notes,
                latitude: $observation->latitude(),
                longitude: $observation->longitude(),
                accuracy: $observation->accuracy(),
                altitude: $observation->altitude(),
                altitudeAccuracy: $observation->altitudeAccuracy(),
                locationSource: $observation->locationSource(),
                bearing: new Bearing($request->bearing),
                updatedAt: new \DateTimeImmutable(),
                isDeleted: $observation->isDeleted(),
            );

            $this->sessionRepository->save($session);
            $this->transactionManager->commit();
        } catch (\DomainException|\InvalidArgumentException|\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
