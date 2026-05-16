<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Entity;

use DateTimeImmutable;

class CronState
{
    public function __construct(
        private string            $jobName,
        private DateTimeImmutable $lastProcessedAt,
    ) {}

    public function jobName(): string
    {
        return $this->jobName;
    }

    public function lastProcessedAt(): DateTimeImmutable
    {
        return $this->lastProcessedAt;
    }

    public function advance(DateTimeImmutable $at): void
    {
        $this->lastProcessedAt = $at;
    }
}
