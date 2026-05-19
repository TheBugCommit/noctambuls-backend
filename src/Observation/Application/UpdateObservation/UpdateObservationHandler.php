<?php

declare(strict_types=1);

namespace App\Observation\Application\UpdateObservation;

use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\Accuracy;
use App\Observation\Domain\ValueObject\Altitude;
use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;

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

        if ($request->latitude !== null && $request->longitude !== null) {
            $latitude = new Latitude($request->latitude);
            $longitude = new Longitude($request->longitude);
            $accuracy = new Accuracy($request->accuracy ?? 0.0);
            $altitude = new Altitude(0.0);
            $altitudeAccuracy = new AltitudeAccuracy(0.0);
            $locationSource = LocationSource::from($request->locationSource ?? LocationSource::Manual->value);
        } else {
            $latitude = $observation->latitude();
            $longitude = $observation->longitude();
            $accuracy = $observation->accuracy();
            $altitude = $observation->altitude();
            $altitudeAccuracy = $observation->altitudeAccuracy();
            $locationSource = $observation->locationSource();
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
                latitude: $latitude,
                longitude: $longitude,
                accuracy: $accuracy,
                altitude: $altitude,
                altitudeAccuracy: $altitudeAccuracy,
                locationSource: $locationSource,
                bearing: new Bearing($request->bearing),
                updatedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
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
