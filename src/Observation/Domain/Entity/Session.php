<?php

declare(strict_types=1);

namespace App\Observation\Domain\Entity;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Domain\Exception\InvalidSessionStateException;
use App\Observation\Domain\ValueObject\Accuracy;
use App\Observation\Domain\ValueObject\Altitude;
use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use App\Observation\Domain\ValueObject\ListeningPoint;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\ObservationId;
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Session
{
    /** @var Collection<int, Observation> */
    private Collection $observations;

    /** @var Collection<int, SessionParticipant> */
    private Collection $participants;

    public function __construct(
        private readonly SessionId          $id,
        private ?CensusId                   $censusId,
        private readonly AuthUserId         $annotatorId,
        private SessionType                 $sessionType,
        private ?\DateTimeImmutable         $startTime,
        private ?\DateTimeImmutable         $endTime,
        private TemperatureRange            $temperature,
        private WindLevel                   $wind,
        private SkyCondition                $sky,
        private RainLevel                   $rain,
        private VisibilityLevel             $visibility,
        private MoonPresence                $moon,
        private ?ListeningPoint             $listeningPoint,
        private ?Latitude                   $latitude,
        private ?Longitude                  $longitude,
        private ?Accuracy                   $accuracy,
        private ?Altitude                   $altitude,
        private ?AltitudeAccuracy           $altitudeAccuracy,
        private ?LocationSource             $locationSource,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable          $updatedAt,
        private bool                        $isDeleted = false,
    )
    {
        $this->participants = new ArrayCollection();
        $this->observations = new ArrayCollection();
    }

    public static function create(
        SessionId           $id,
        ?CensusId           $censusId,
        AuthUserId          $annotatorId,
        SessionType         $sessionType,
        ?\DateTimeImmutable $startTime,
        ?\DateTimeImmutable $endTime,
        TemperatureRange    $temperature,
        WindLevel           $wind,
        SkyCondition        $sky,
        RainLevel           $rain,
        VisibilityLevel     $visibility,
        MoonPresence        $moon,
        ?ListeningPoint     $listeningPoint,
        ?Latitude           $latitude,
        ?Longitude          $longitude,
        ?Accuracy           $accuracy,
        ?Altitude           $altitude,
        ?AltitudeAccuracy   $altitudeAccuracy,
        ?LocationSource     $locationSource,
        \DateTimeImmutable  $createdAt,
        \DateTimeImmutable  $updatedAt,
    ): self
    {
        $session = new self(
            $id, $censusId, $annotatorId, $sessionType, $startTime, $endTime,
            $temperature, $wind, $sky, $rain, $visibility, $moon,
            $listeningPoint, $latitude, $longitude, $accuracy, $altitude, $altitudeAccuracy, $locationSource,
            $createdAt, $updatedAt,
        );
        $session->assertValidState();
        return $session;
    }

    public function update(
        ?CensusId           $censusId,
        SessionType         $sessionType,
        ?\DateTimeImmutable $startTime,
        ?\DateTimeImmutable $endTime,
        TemperatureRange    $temperature,
        WindLevel           $wind,
        SkyCondition        $sky,
        RainLevel           $rain,
        VisibilityLevel     $visibility,
        MoonPresence        $moon,
        ?ListeningPoint     $listeningPoint,
        ?Latitude           $latitude,
        ?Longitude          $longitude,
        ?Accuracy           $accuracy,
        ?Altitude           $altitude,
        ?AltitudeAccuracy   $altitudeAccuracy,
        ?LocationSource     $locationSource,
        \DateTimeImmutable  $updatedAt,
    ): void
    {
        $this->censusId = $censusId;
        $this->sessionType = $sessionType;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->temperature = $temperature;
        $this->wind = $wind;
        $this->sky = $sky;
        $this->rain = $rain;
        $this->visibility = $visibility;
        $this->moon = $moon;
        $this->listeningPoint = $listeningPoint;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;
        $this->altitude = $altitude;
        $this->altitudeAccuracy = $altitudeAccuracy;
        $this->locationSource = $locationSource;
        $this->updatedAt = $updatedAt;
        $this->assertValidState();
    }

    public function id(): SessionId
    {
        return $this->id;
    }

    public function censusId(): ?CensusId
    {
        return $this->censusId;
    }

    public function annotatorId(): AuthUserId
    {
        return $this->annotatorId;
    }

    public function sessionType(): SessionType
    {
        return $this->sessionType;
    }

    public function startTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function endTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function temperature(): TemperatureRange
    {
        return $this->temperature;
    }

    public function wind(): WindLevel
    {
        return $this->wind;
    }

    public function sky(): SkyCondition
    {
        return $this->sky;
    }

    public function rain(): RainLevel
    {
        return $this->rain;
    }

    public function visibility(): VisibilityLevel
    {
        return $this->visibility;
    }

    public function moon(): MoonPresence
    {
        return $this->moon;
    }

    public function listeningPoint(): ?ListeningPoint
    {
        return $this->listeningPoint;
    }

    public function latitude(): ?Latitude
    {
        return $this->latitude;
    }

    public function longitude(): ?Longitude
    {
        return $this->longitude;
    }

    public function accuracy(): ?Accuracy
    {
        return $this->accuracy;
    }

    public function altitude(): ?Altitude
    {
        return $this->altitude;
    }

    public function altitudeAccuracy(): ?AltitudeAccuracy
    {
        return $this->altitudeAccuracy;
    }

    public function locationSource(): ?LocationSource
    {
        return $this->locationSource;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function markDeleted(\DateTimeImmutable $at): void
    {
        $this->isDeleted = true;
        $this->updatedAt = $at;

        foreach ($this->observations as $observation) {
            if (!$observation->isDeleted()) {
                $observation->markDeleted($at);
            }
        }
    }

    public function restore(\DateTimeImmutable $at): void
    {
        $this->isDeleted = false;
        $this->updatedAt = $at;

        foreach ($this->observations as $observation) {
            $observation->restore($at);
        }
    }

    public function hasParticipantWithCode(OrnithologistCode $code): bool
    {
        foreach ($this->participants as $existingParticipant) {
            if ($existingParticipant->ornithologistCode()->value() === $code->value()) {
                return true;
            }
        }

        return false;
    }

    public function addParticipant(SessionParticipant $participant, \DateTimeImmutable $at): void
    {
        if (!$this->hasParticipantWithCode($participant->ornithologistCode())) {
            $this->participants->add($participant);
        }
        if ($at > $this->updatedAt) {
            $this->updatedAt = $at;
        }
    }

    public function removeParticipant(SessionParticipant $participant, \DateTimeImmutable $at): void
    {
        $this->participants->removeElement($participant);
        if ($at > $this->updatedAt) {
            $this->updatedAt = $at;
        }
    }

    public function clearParticipants(\DateTimeImmutable $at): void
    {
        $this->participants->clear();
        if ($at > $this->updatedAt) {
            $this->updatedAt = $at;
        }
    }

    /** @return Collection<int, SessionParticipant> */
    public function participants(): Collection
    {
        return $this->participants;
    }

    public function putObservation(Observation $observation): void
    {
        if (!$observation->isDeleted()) {
            $this->assertObservationWithinBounds($observation);
            $this->assertAndPrepareObservationLocation($observation);
        }

        $existing = $this->findObservationById($observation->id());

        if ($existing !== null) {
            $existing->update(
                speciesId: $observation->speciesId(),
                contactType: $observation->contactType(),
                quantity: $observation->quantity(),
                startTime: $observation->startTime(),
                endTime: $observation->endTime(),
                notes: $observation->notes(),
                latitude: $observation->latitude(),
                longitude: $observation->longitude(),
                accuracy: $observation->accuracy(),
                altitude: $observation->altitude(),
                altitudeAccuracy: $observation->altitudeAccuracy(),
                locationSource: $observation->locationSource(),
                bearing: $observation->bearing(),
                updatedAt: $observation->updatedAt(),
                isDeleted: $observation->isDeleted(),
            );
        } else {
            $this->observations->add($observation);
        }

        if ($observation->updatedAt() > $this->updatedAt) {
            $this->updatedAt = $observation->updatedAt();
        }
    }

    public function clearObservations(): void
    {
        $this->observations->clear();
    }

    public function removeObservation(Observation $observation): void
    {
        $this->observations->removeElement($observation);
    }

    /** @return Collection<int, Observation> */
    public function observations(): Collection
    {
        return $this->observations;
    }

    private function assertObservationWithinBounds(Observation $observation): void
    {
        if ($this->startTime === null) {
            throw new InvalidSessionStateException('Session must have a start time to validate observation bounds');
        }

        if ($this->sessionType === SessionType::Station || $this->sessionType === SessionType::Census) {
            if ($this->endTime === null) {
                throw new InvalidSessionStateException('Session must have an end time to validate observation bounds');
            }

            if ($observation->startTime() < $this->startTime || $observation->endTime() > $this->endTime) {
                throw new InvalidSessionStateException('Observation must be strictly within the session time range');
            }
        } elseif ($this->sessionType === SessionType::Incidental) {
            $minAllowedStart = (clone $this->startTime)->modify('-1 hour');

            $maxAllowedEnd = (clone $this->startTime)->modify('+48 hours');

            if ($observation->startTime() < $minAllowedStart || $observation->endTime() > $maxAllowedEnd) {
                throw new InvalidSessionStateException('Incidental observations must occur within the logical day of the session (including next-day early hours).');
            }
        }
    }

    private function assertAndPrepareObservationLocation(Observation $observation): void
    {
        if ($this->sessionType === SessionType::Incidental) {
            if ($observation->latitude() === null || $observation->longitude() === null) {
                throw new InvalidSessionStateException('Incidentals observations must have location.');
            }
        }
        else {
            if ($observation->latitude() === null || $observation->longitude() === null) {

                if ($this->latitude === null || $this->longitude === null) {
                    throw new InvalidSessionStateException('Can heradate ubications becouse session doesnt have.');
                }

                $observation->inheritLocation(
                    $this->latitude,
                    $this->longitude,
                    $this->accuracy,
                    $this->altitude,
                    $this->altitudeAccuracy,
                    $this->locationSource
                );
            }
        }
    }

    private function assertValidState(): void
    {
        if ($this->sessionType === SessionType::Census) {
            if ($this->censusId === null) {
                throw new InvalidSessionStateException('Census Id required');
            }

        }

        if ($this->sessionType === SessionType::Station || $this->sessionType === SessionType::Census) {
            if ($this->listeningPoint === null) {
                throw new InvalidSessionStateException('Listening Point required');
            }
            if ($this->latitude === null || $this->longitude === null) {
                throw new InvalidSessionStateException('Location (lat, long) required');
            }
            if ($this->locationSource === null) {
                throw new InvalidSessionStateException('Location Source required');
            }
            if ($this->startTime() === null || $this->endTime() === null) {
                throw new InvalidSessionStateException('Start Time and End Time required');
            }
            if ($this->startTime > $this->endTime) {
                throw new InvalidSessionStateException('Session Start Time cannot be greater than End Time');
            }
        }

        if($this->createdAt > $this->updatedAt){
            throw new InvalidSessionStateException();
        }
    }

    public function findObservationById(ObservationId $id): ?Observation
    {
        foreach ($this->observations as $obs) {
            if ($obs->id()->value() === $id->value()) {
                return $obs;
            }
        }
        return null;
    }
}
