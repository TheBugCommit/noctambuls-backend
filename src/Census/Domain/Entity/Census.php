<?php

declare(strict_types=1);

namespace App\Census\Domain\Entity;

use App\Census\Domain\Exception\CensusNotEditableException;
use App\Census\Domain\ValueObject\CensusDescription;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusStatus;
use App\Census\Domain\ValueObject\CensusTitle;

class Census
{
    public function __construct(
        private readonly CensusId $id,
        private CensusTitle $title,
        private \DateTimeImmutable $date,
        private \DateTimeImmutable $time,
        private CensusLocationName $locationName,
        private ?CensusDescription $description,
        private CensusStatus $status,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $publishedAt = null,
    ) {
    }

    public static function create(
        CensusId $id,
        CensusTitle $title,
        \DateTimeImmutable $date,
        \DateTimeImmutable $time,
        CensusLocationName $locationName,
        ?CensusDescription $description,
    ): self {
        $now = new \DateTimeImmutable();

        return new self($id, $title, $date, $time, $locationName, $description, CensusStatus::DRAFT, $now, $now);
    }

    public function update(
        CensusTitle $title,
        \DateTimeImmutable $date,
        \DateTimeImmutable $time,
        CensusLocationName $locationName,
        ?CensusDescription $description,
    ): void {
        $this->assertEditable();

        $this->title = $title;
        $this->date = $date;
        $this->time = $time;
        $this->locationName = $locationName;
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->status = CensusStatus::PUBLISHED;
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isEditable(): bool
    {
        if ($this->status === CensusStatus::DRAFT) {
            return true;
        }

        return $this->publishedAt !== null
            && $this->publishedAt->modify('+10 minutes') > new \DateTimeImmutable();
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

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function time(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function locationName(): CensusLocationName
    {
        return $this->locationName;
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
