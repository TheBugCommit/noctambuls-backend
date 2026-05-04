<?php

declare(strict_types=1);

namespace App\Auth\Application\UnblockUser;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserAlreadyAnonymizedException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class UnblockUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(UnblockUserRequest $request): UnblockUserResponse
    {
        $this->transactionManager->begin();

        try {
            $user = $this->userRepository->findById(new AuthUserId($request->userId));

            if ($user === null) {
                throw new UserNotFoundException();
            }

            if ($user->isAnonymized()) {
                throw new UserAlreadyAnonymizedException();
            }

            $user->unblock();
            $this->transactionManager->commit();

            return AuthResponseMapper::toUnblockUserResponse($request->userId);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
