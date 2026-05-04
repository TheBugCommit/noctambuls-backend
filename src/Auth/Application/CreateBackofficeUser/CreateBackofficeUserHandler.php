<?php

declare(strict_types=1);

namespace App\Auth\Application\CreateBackofficeUser;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;

final readonly class CreateBackofficeUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository,
        private PasswordHasherInterface     $passwordHasher,
        private UuidGeneratorInterface      $uuidGenerator,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(CreateBackofficeUserRequest $request): CreateBackofficeUserResponse
    {
        $this->transactionManager->begin();

        try {
            $email = new Email($request->email);

            $existing = $this->userRepository->findByEmailAndProvider($email, Provider::internal());
            if ($existing !== null) {
                throw new UserAlreadyExistsException();
            }

            $role = Role::tryFrom($request->role);
            $plainPassword = new PlainPassword($request->plainPassword);
            $user = AuthUser::createBackofficeUser(
                new AuthUserId($this->uuidGenerator->generate()),
                $email,
                $role,
                new HashedPassword($this->passwordHasher->hash($plainPassword->value())),
            );

            $this->userRepository->save($user);
            $this->transactionManager->commit();

            return AuthResponseMapper::toCreateBackofficeUserResponse($user->id()->value());
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
