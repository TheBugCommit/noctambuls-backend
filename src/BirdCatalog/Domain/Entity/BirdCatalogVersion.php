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
        return new self(1, 0, new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
    }

    public function increment(): void
    {
        $this->version++;
        $this->lastUpdatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
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
