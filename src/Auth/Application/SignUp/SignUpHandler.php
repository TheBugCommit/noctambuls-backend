<?php

declare(strict_types=1);

namespace App\Auth\Application\SignUp;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Application\SignIn\RefreshTokenIssuerInterface;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Provider;
use App\Profile\Application\Service\CreateUserProfileService;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Throwable;

final readonly class SignUpHandler
{
    public function __construct(
        private ExternalTokenValidatorInterface $tokenValidator,
        private AuthUserRepositoryInterface     $userRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private RefreshTokenIssuerInterface     $refreshTokenIssuer,
        private TransactionManagerInterface     $transactionManager,
        private UuidGeneratorInterface          $uuidGenerator,
        private CreateUserProfileService        $createUserProfileService,
    ) {
    }

    /** @throws Throwable */
    public function handle(SignUpRequest $request): SignUpResponse
    {
        $provider = new Provider($request->providerType);
        $email = $this->tokenValidator->validate($request->externalToken, $provider);

        if ($this->userRepository->findByEmailAndProvider($email, $provider) !== null) {
            throw new UserAlreadyExistsException();
        }

        $uuid = $this->uuidGenerator->generate();

        $user = AuthUser::create(
            new AuthUserId($uuid),
            $email,
            $provider,
        );

        $this->transactionManager->begin();
        try {
            $this->userRepository->save($user);

            $this->createUserProfileService->execute($uuid, $request->name, $request->phone);

            $accessToken = $this->jwtGenerator->generate($user);
            $refreshToken = $this->refreshTokenIssuer->issueTokenForUser($user);

            $this->transactionManager->commit();

            return AuthResponseMapper::toSignUpResponse($accessToken, $refreshToken);
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
