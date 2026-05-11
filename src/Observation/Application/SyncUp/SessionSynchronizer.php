<?php

namespace App\Observation\Application\SyncUp;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Entity\SessionParticipant;
use App\Observation\Domain\Exception\InvalidSessionStateException;
use App\Observation\Domain\ValueObject\Accuracy;
use App\Observation\Domain\ValueObject\Altitude;
use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\ListeningPoint;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use DateMalformedStringException;
use DomainException;

readonly class SessionSynchronizer
{
    public function __construct(
        private CensusRepositoryInterface $censusRepository
    )
    {
    }

    /**
     * @throws SyncSkippedException
     * @throws SyncValidationException
     * @throws DomainException|DateMalformedStringException
     */
    public function sync(SessionSyncDto $dto, string $annotatorId, ?Session $existingSession, array $validCodesSet, array $validBirdsSet): Session
    {
        $incomingUpdatedAt = new \DateTimeImmutable($dto->updatedAt);

        if ($existingSession !== null && $existingSession->updatedAt() >= $incomingUpdatedAt) {
            throw new SyncSkippedException();
        }

        $this->ensureCensusExistsIfRequired($dto);

        $session = $existingSession ?? clone $this->createSession($dto, $annotatorId);

        if ($existingSession !== null) {
            $this->updateSession($session, $dto, $incomingUpdatedAt);
        }

        $this->syncObservations($session, $dto, $validBirdsSet);
        $this->syncParticipants($session, $dto, $validCodesSet);

        return $session;
    }

    private function ensureCensusExistsIfRequired(SessionSyncDto $dto): void
    {
        $sessionType = SessionType::tryFrom($dto->sessionType);
        if ($sessionType == null) {
            throw new InvalidSessionStateException('Session Type is not defined');
        }

        if ($sessionType === SessionType::Census) {
            if ($dto->censusId === null || !$this->censusRepository->findById(new CensusId($dto->censusId))) {
                throw new CensusNotFoundException();
            }
        }
    }

    private function createSession(SessionSyncDto $dto, string $annotatorId): Session
    {
        return Session::create(
            id: new SessionId($dto->id),
            censusId: $dto->censusId ? new CensusId($dto->censusId) : null,
            annotatorId: new AuthUserId($annotatorId),
            sessionType: SessionType::from($dto->sessionType),
            startTime: $dto->startTime ? new \DateTimeImmutable($dto->startTime) : null,
            endTime: $dto->endTime ? new \DateTimeImmutable($dto->endTime) : null,
            temperature: TemperatureRange::from($dto->temperature),
            wind: WindLevel::from($dto->wind),
            sky: SkyCondition::from($dto->sky),
            rain: RainLevel::from($dto->rain),
            visibility: VisibilityLevel::from($dto->visibility),
            moon: MoonPresence::from($dto->moon),
            listeningPoint: $dto->listeningPoint !== null ? new ListeningPoint($dto->listeningPoint) : null,
            latitude: $dto->latitude !== null ? new Latitude($dto->latitude) : null,
            longitude: $dto->longitude !== null ? new Longitude($dto->longitude) : null,
            accuracy: $dto->accuracy !== null ? new Accuracy($dto->accuracy) : null,
            altitude: $dto->altitude !== null ? new Altitude($dto->altitude) : null,
            altitudeAccuracy: $dto->altitudeAccuracy !== null ? new AltitudeAccuracy($dto->altitudeAccuracy) : null,
            locationSource: $dto->locationSource !== null ? LocationSource::from($dto->locationSource) : null,
            createdAt: new \DateTimeImmutable($dto->createdAt),
            updatedAt: new \DateTimeImmutable($dto->updatedAt),
        );
    }

    private function updateSession(Session $session, SessionSyncDto $dto, \DateTimeImmutable $updatedAt): void
    {
        $session->update(
            censusId: $dto->censusId ? new CensusId($dto->censusId) : null,
            sessionType: SessionType::from($dto->sessionType),
            startTime: $dto->startTime ? new \DateTimeImmutable($dto->startTime) : null,
            endTime: $dto->endTime ? new \DateTimeImmutable($dto->endTime) : null,
            temperature: TemperatureRange::from($dto->temperature),
            wind: WindLevel::from($dto->wind),
            sky: SkyCondition::from($dto->sky),
            rain: RainLevel::from($dto->rain),
            visibility: VisibilityLevel::from($dto->visibility),
            moon: MoonPresence::from($dto->moon),
            listeningPoint: $dto->listeningPoint !== null ? new ListeningPoint($dto->listeningPoint) : null,
            latitude: $dto->latitude !== null ? new Latitude($dto->latitude) : null,
            longitude: $dto->longitude !== null ? new Longitude($dto->longitude) : null,
            accuracy: $dto->accuracy !== null ? new Accuracy($dto->accuracy) : null,
            altitude: $dto->altitude !== null ? new Altitude($dto->altitude) : null,
            altitudeAccuracy: $dto->altitudeAccuracy !== null ? new AltitudeAccuracy($dto->altitudeAccuracy) : null,
            locationSource: $dto->locationSource !== null ? LocationSource::from($dto->locationSource) : null,
            updatedAt: $updatedAt,
        );
    }

    private function syncObservations(Session $session, SessionSyncDto $dto): void
    {
        $session->clearObservations();

        foreach ($dto->observations as $obsDto) {
            if (!isset($validBirdsSet[$obsDto->speciesId])) {
                throw new SyncValidationException('invalid_species_id');
            }

            $observation = Observation::create(
                id: new ObservationId($obsDto->id),
                session: $session,
                speciesId: new BirdId($obsDto->speciesId),
                contactType: ContactType::from($obsDto->contactType),
                quantity: new Quantity($obsDto->quantity),
                startTime: new \DateTimeImmutable($obsDto->startTime),
                endTime: new \DateTimeImmutable($obsDto->endTime),
                notes: $obsDto->notes,
                latitude: $obsDto->latitude !== null ? new Latitude($obsDto->latitude) : null,
                longitude: $obsDto->longitude !== null ? new Longitude($obsDto->longitude) : null,
                accuracy: $obsDto->accuracy !== null ? new Accuracy($obsDto->accuracy) : null,
                altitude: $obsDto->altitude !== null ? new Altitude($obsDto->altitude) : null,
                altitudeAccuracy: $obsDto->altitudeAccuracy !== null ? new AltitudeAccuracy($obsDto->altitudeAccuracy) : null,
                locationSource: $obsDto->locationSource !== null ? LocationSource::from($obsDto->locationSource) : null,
                bearing: new Bearing($obsDto->bearing),
                createdAt: new \DateTimeImmutable($obsDto->createdAt),
                updatedAt: new \DateTimeImmutable($obsDto->updatedAt),
            );

            $session->addObservation($observation);
        }
    }

    private function syncParticipants(Session $session, SessionSyncDto $dto, array $validCodesSet): void
    {
        $session->clearParticipants();

        foreach ($dto->participants as $participantDto) {
            if (isset($validCodesSet[$participantDto->ornithologistCode])) {
                $session->addParticipant(
                    SessionParticipant::create(
                        $session,
                        new OrnithologistCode($participantDto->ornithologistCode)
                    )
                );
            }
        }
    }
}
