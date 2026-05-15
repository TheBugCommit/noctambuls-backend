<?php

declare(strict_types=1);

namespace App\Census\Domain\Entity;

use App\Census\Domain\Exception\CensusNotEditableException;
use App\Census\Domain\ValueObject\CensusDate;
use App\Census\Domain\ValueObject\CensusDescription;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusStatus;
use App\Census\Domain\ValueObject\CensusTime;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;

class Census
{
    public function __construct(
        private readonly CensusId           $id,
        private CensusTitle                 $title,
        private CensusDate                  $date,
        private CensusTime                  $time,
        private Latitude                    $latitude,
        private Longitude                   $longitude,
        private CensusLocationName          $locationName,
        private ?CensusDescription          $description,
        private CensusStatus                $status,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable          $updatedAt,
        private ?\DateTimeImmutable         $publishedAt = null,
    ) {
    }

    public static function create(
        CensusId           $id,
        CensusTitle        $title,
        CensusDate         $date,
        CensusTime         $time,
        CensusLocationName $locationName,
        Latitude           $latitude,
        Longitude          $longitude,
        ?CensusDescription $description,
    ): self {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return new self($id, $title, $date, $time, $latitude, $longitude, $locationName, $description, CensusStatus::DRAFT, $now, $now);
    }

    public function update(
        CensusTitle        $title,
        CensusDate         $date,
        CensusTime         $time,
        CensusLocationName $locationName,
        Latitude           $latitude,
        Longitude          $longitude,
        ?CensusDescription $description,
    ): void {
        $this->assertEditable();

        $this->title = $title;
        $this->date = $date;
        $this->time = $time;
        $this->locationName = $locationName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function publish(): void
    {
        $this->status = CensusStatus::PUBLISHED;
        $this->publishedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function isEditable(): bool
    {
        if ($this->status === CensusStatus::DRAFT) {
            return true;
        }

        return $this->publishedAt !== null
            && $this->publishedAt->modify('+10 minutes') > new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function assertEditable(): void
    {
        if (!$this->isEditable()) {
            throw new CensusNotEditableException();
        }
    }

    public function id(): CensusId
    {
        return $this->id;
    }

    public function title(): CensusTitle
    {
        return $this->title;
    }

    public function date(): CensusDate
    {
        return $this->date;
    }

    public function time(): CensusTime
    {
        return $this->time;
    }

    public function locationName(): CensusLocationName
    {
        return $this->locationName;
    }

    public function longitude(): Longitude
    {
        return $this->longitude;
    }

    public function latitude(): Latitude
    {
        return $this->latitude;
    }

    public function description(): ?CensusDescription
    {
        return $this->description;
    }

    public function status(): CensusStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
