<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

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
        $email = $this->tokenValidator->validate($request->externalToken);
        $provider = new Provider($request->providerType);

        $this->transactionManager->begin();
        try {
            $user = $this->userRepository->findByEmailAndProvider($email, $provider);

            if ($user === null) {
                throw new UserNotRegisteredException();
            }

            $accessToken = $this->jwtGenerator->generate($user);
            $refreshToken = $this->refreshTokenIssuer->issueTokenForUser($user);

            $response = new SignInResponse(
                $accessToken,
                $refreshToken,
            );

            $this->transactionManager->commit();

            return $response;

        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
