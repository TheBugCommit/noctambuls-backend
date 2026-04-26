<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Entity;

class BirdCatalogVersion
{
    public function __construct(
        private readonly int        $id,
        private int                 $version,
        private \DateTimeImmutable  $lastUpdatedAt,
    ) {
    }

    public static function initialize(): self
    {
        return new self(1, 0, new \DateTimeImmutable());
    }

    public function increment(): void
    {
        $this->version++;
        $this->lastUpdatedAt = new \DateTimeImmutable();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function lastUpdatedAt(): \DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }
}
