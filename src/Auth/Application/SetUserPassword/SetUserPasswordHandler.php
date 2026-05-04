<?php

declare(strict_types=1);

namespace App\Auth\Application\SetUserPassword;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class SetUserPasswordHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $userRepository,
        private PasswordHasherInterface     $passwordHasher,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(SetUserPasswordRequest $request): SetUserPasswordResponse
    {
        $this->transactionManager->begin();
        try {
            $user = $this->userRepository->findById(new AuthUserId($request->userId));
            if ($user === null) throw new UserNotFoundException();

            $hashedString = $this->passwordHasher->hash($request->plainPassword);

            $user->updateHashedPassword(new HashedPassword($hashedString));

            $this->transactionManager->commit();

            return AuthResponseMapper::toSetUserPasswordResponse($request->userId);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
