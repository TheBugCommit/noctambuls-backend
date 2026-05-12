<?php

declare(strict_types=1);

namespace App\Observation\Application\RestoreSession;

use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class RestoreSessionHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private TransactionManagerInterface $transactionManager,

    ) {
    }

    public function handle(RestoreSessionRequest $request): void
    {
        $sessions = $this->sessionRepository->findByIdsWithRelations([$request->sessionId]);
        $session = $sessions[0] ?? null;

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $this->transactionManager->begin();
        try {
            $session->restore(new \DateTimeImmutable());
            $this->sessionRepository->save($session);
            $this->transactionManager->commit();
        } catch (\DomainException|\InvalidArgumentException|\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
