<?php

declare(strict_types=1);

namespace App\Auth\Application\AnonymizeUser;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserAlreadyAnonymizedException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class AnonymizeUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private ProfileRepositoryInterface  $profileRepository,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    public function handle(AnonymizeUserRequest $request): AnonymizeUserResponse
    {
        $this->transactionManager->begin();

        try {
            $user = $this->authUserRepository->findById(new AuthUserId($request->userId));

            if ($user === null) {
                throw new UserNotFoundException();
            }

            if ($user->isAnonymized()) {
                throw new UserAlreadyAnonymizedException();
            }

            $originalEmail = $user->email();

            $anonymizedEmail = new Email("$request->userId@deleted.noctambuls.local");
            $user->anonymize($anonymizedEmail);

            $profile = $this->profileRepository->findById(new ProfileId($request->userId));
            $profile?->anonymize();

            $this->authUserRepository->deleteRefreshTokensByEmail($originalEmail);

            $this->transactionManager->commit();

            return AuthResponseMapper::toAnonymizeUserResponse($request->userId);
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
