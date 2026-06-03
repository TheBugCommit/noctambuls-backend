<?php

declare(strict_types=1);

namespace App\Observation\Application\PurgeExpiredDeletedSessions;

use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\Service\SessionEventPublisherInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class PurgeExpiredDeletedSessionsHandler
{
    public function __construct(
        private SessionRepositoryInterface     $sessionRepository,
        private TransactionManagerInterface    $transactionManager,
        private SessionEventPublisherInterface $eventPublisher,
    ) {
    }

    public function handle(\DateTimeImmutable $cutoff): int
    {
        $this->transactionManager->begin();
        try {
            $this->eventPublisher->publishBeforeSessionsPurged($cutoff);
            $count = $this->sessionRepository->purgeDeletedBefore($cutoff);
            $this->transactionManager->commit();

            return $count;
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
