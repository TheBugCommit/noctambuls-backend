<?php

declare(strict_types=1);

namespace App\Auth\Application\ChangeUserRole;

use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class ChangeUserRoleHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository, private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(ChangeUserRoleRequest $request): ChangeUserRoleResponse
    {
        $this->transactionManager->begin();

        try {
            $userId = new AuthUserId($request->userId);
            $user = $this->userRepository->findById($userId);

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $role = Role::tryFrom($request->role);
            if ($role === null) {
                throw new \InvalidArgumentException(sprintf('Invalid role: "%s".', $request->role));
            }

            $user->changeRole($role);
            $this->transactionManager->commit();

            return new ChangeUserRoleResponse($request->userId, $role->value);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
