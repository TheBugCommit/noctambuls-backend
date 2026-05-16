<?php

declare(strict_types=1);

namespace App\Observation\Application\PurgeDeletedSessions;

use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\Service\SessionEventPublisherInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class PurgeDeletedSessionsHandler
{
    public function __construct(
        private SessionRepositoryInterface     $sessionRepository,
        private TransactionManagerInterface    $transactionManager,
        private SessionEventPublisherInterface $eventPublisher,
    ) {
    }

    public function handle(): int
    {
        $this->transactionManager->begin();
        try {
            $this->eventPublisher->publishBeforeSessionsPurged();
            $count = $this->sessionRepository->purgeDeleted();
            $this->transactionManager->commit();

            return $count;
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
