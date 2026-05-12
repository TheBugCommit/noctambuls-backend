<?php

declare(strict_types=1);

namespace App\Observation\Application\SoftDeleteSession;

use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class SoftDeleteSessionHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private TransactionManagerInterface $transactionManager,

    ) {
    }

    public function handle(SoftDeleteSessionRequest $request): void
    {
        $sessions = $this->sessionRepository->findByIdsWithRelations([$request->sessionId]);
        $session = $sessions[0] ?? null;

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $this->transactionManager->begin();
        try {
            $session->markDeleted(new \DateTimeImmutable());
            $this->sessionRepository->save($session);
            $this->transactionManager->commit();
        } catch (\DomainException|\InvalidArgumentException|\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
