<?php

declare(strict_types=1);

namespace App\Auth\Application\UpdateBackofficeUser;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class UpdateBackofficeUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository,
        private PasswordHasherInterface     $passwordHasher,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(UpdateBackofficeUserRequest $request): UpdateBackofficeUserResponse
    {
        $this->transactionManager->begin();

        try {
            $user = $this->userRepository->findById(new AuthUserId($request->userId));

            if ($user === null) {
                throw new UserNotFoundException();
            }

            if ($request->email !== null) {
                $newEmail = new Email($request->email);
                $existing = $this->userRepository->findByEmail($newEmail);
                if ($existing !== null && $existing->id()->value() !== $request->userId) {
                    throw new UserAlreadyExistsException();
                }
                $user->changeEmail($newEmail);
            }

            if ($request->plainPassword !== null) {
                if (strlen($request->plainPassword) < 8) {
                    throw new \InvalidArgumentException('Password must be at least 8 characters.');
                }
                $user->updateHashedPassword(new HashedPassword($this->passwordHasher->hash($request->plainPassword)));
            }

            if ($request->role !== null) {
                $role = Role::tryFrom($request->role);
                if ($role === null || $role === Role::USER) {
                    throw new \InvalidArgumentException(sprintf('Invalid backoffice role: "%s"', $request->role));
                }
                $user->changeRole($role);
            }

            $this->transactionManager->commit();

            return AuthResponseMapper::toUpdateBackofficeUserResponse($request->userId);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
