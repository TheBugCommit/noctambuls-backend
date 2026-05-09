<?php

declare(strict_types=1);

namespace App\Auth\Application\DeleteBackofficeUser;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class DeleteBackofficeUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(DeleteBackofficeUserRequest $request): DeleteBackofficeUserResponse
    {
        $this->transactionManager->begin();

        try {
            $user = $this->userRepository->findById(new AuthUserId($request->userId));

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $this->userRepository->deleteRefreshTokensByEmail($user->email());
            $this->userRepository->delete($user);
            $this->transactionManager->commit();

            return AuthResponseMapper::toDeleteBackofficeUserResponse($request->userId);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
