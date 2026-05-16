<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\ValueObject\CronJobName;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCronStateRepository implements CronStateRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByJobName(CronJobName $jobName): ?CronState
    {
        return $this->em->find(CronState::class, $jobName);
    }

    public function save(CronState $cronState): void
    {
        $this->em->persist($cronState);
    }
}
