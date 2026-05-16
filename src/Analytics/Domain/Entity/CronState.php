<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Entity;

use App\Analytics\Domain\ValueObject\CronJobName;
use DateTimeImmutable;

class CronState
{
    public function __construct(
        private readonly CronJobName $jobName,
        private DateTimeImmutable    $lastProcessedAt,
    ) {}

    public function jobName(): CronJobName
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
