<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

use App\Analytics\Domain\Entity\CronState;

interface CronStateRepositoryInterface
{
    public function findByJobName(string $jobName): ?CronState;

    public function save(CronState $cronState): void;
}
