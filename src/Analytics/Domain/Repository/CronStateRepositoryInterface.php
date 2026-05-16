<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\ValueObject\CronJobName;

interface CronStateRepositoryInterface
{
    public function findByJobName(CronJobName $jobName): ?CronState;

    public function save(CronState $cronState): void;
}
