<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Exception\UserBlockedException;
use App\Auth\Domain\Exception\UserNotRegisteredException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class SignInHandler implements SignInHandlerInterface
{
    public function __construct(
        private ExternalTokenValidatorInterface $tokenValidator,
        private AuthUserRepositoryInterface     $userRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private TransactionManagerInterface     $transactionManager,
        private RefreshTokenIssuerInterface     $refreshTokenIssuer,
    ) {
    }

    /** @throws Throwable */
    public function handle(SignInRequest $request): SignInResponse
    {
        $provider = new Provider($request->providerType);
        $email = $this->tokenValidator->validate($request->externalToken, $provider);

        $this->transactionManager->begin();
        try {
            $user = $this->userRepository->findByEmailAndProvider($email, $provider);

            if ($user === null) {
                throw new UserNotRegisteredException();
            }

            if ($user->isBlocked()) {
                throw new UserBlockedException();
            }

            $accessToken = $this->jwtGenerator->generate($user);
            $refreshToken = $this->refreshTokenIssuer->issueTokenForUser($user);

            $this->transactionManager->commit();

            return AuthResponseMapper::toSignInResponse($accessToken, $refreshToken);
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
