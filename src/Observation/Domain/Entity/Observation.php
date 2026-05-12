<?php

declare(strict_types=1);

namespace App\Observation\Domain\Entity;

use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Observation\Domain\ValueObject\Accuracy;
use App\Observation\Domain\ValueObject\Altitude;
use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;

class Observation
{
    public function __construct(
        private readonly ObservationId      $id,
        private readonly Session            $session,
        private BirdId                      $speciesId,
        private ContactType                 $contactType,
        private Quantity                    $quantity,
        private \DateTimeImmutable          $startTime,
        private \DateTimeImmutable          $endTime,
        private ?string                     $notes,
        private ?Latitude                   $latitude,
        private ?Longitude                  $longitude,
        private ?Accuracy                   $accuracy,
        private ?Altitude                   $altitude,
        private ?AltitudeAccuracy           $altitudeAccuracy,
        private ?LocationSource             $locationSource,
        private Bearing                     $bearing,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable          $updatedAt,
        private bool                        $isDeleted,
    )
    {
    }

    public static function create(
        ObservationId      $id,
        Session            $session,
        BirdId             $speciesId,
        ContactType        $contactType,
        Quantity           $quantity,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string            $notes,
        ?Latitude          $latitude,
        ?Longitude         $longitude,
        ?Accuracy          $accuracy,
        ?Altitude          $altitude,
        ?AltitudeAccuracy  $altitudeAccuracy,
        ?LocationSource    $locationSource,
        Bearing            $bearing,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        bool               $isDeleted = false,
    ): self
    {
        return new self(
            $id, $session, $speciesId, $contactType, $quantity,
            $startTime, $endTime, $notes,
            $latitude, $longitude, $accuracy, $altitude, $altitudeAccuracy, $locationSource,
            $bearing, $createdAt, $updatedAt, $isDeleted,
        );
    }

    public function update(
        BirdId             $speciesId,
        ContactType        $contactType,
        Quantity           $quantity,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string            $notes,
        ?Latitude          $latitude,
        ?Longitude         $longitude,
        ?Accuracy          $accuracy,
        ?Altitude          $altitude,
        ?AltitudeAccuracy  $altitudeAccuracy,
        ?LocationSource    $locationSource,
        Bearing            $bearing,
        \DateTimeImmutable $updatedAt,
        bool               $isDeleted = false,
    ): void
    {
        $this->speciesId = $speciesId;
        $this->contactType = $contactType;
        $this->quantity = $quantity;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->notes = $notes;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;
        $this->altitude = $altitude;
        $this->altitudeAccuracy = $altitudeAccuracy;
        $this->locationSource = $locationSource;
        $this->bearing = $bearing;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
    }

    public function markDeleted(\DateTimeImmutable $updatedAt): void
    {
        $this->isDeleted = true;
        $this->updatedAt = $updatedAt;
    }

    public function id(): ObservationId
    {
        return $this->id;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function speciesId(): BirdId
    {
        return $this->speciesId;
    }

    public function contactType(): ContactType
    {
        return $this->contactType;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function startTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function endTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function notes(): ?string
    {
        return $this->notes;
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

    public function bearing(): Bearing
    {
        return $this->bearing;
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
}
