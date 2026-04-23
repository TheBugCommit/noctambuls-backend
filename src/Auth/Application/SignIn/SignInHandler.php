<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Throwable;

final readonly class SignInHandler implements SignInHandlerInterface
{
    public function __construct(
        private ExternalTokenValidatorInterface $tokenValidator,
        private AuthUserRepositoryInterface     $userRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private TransactionManagerInterface     $transactionManager,
        private UuidGeneratorInterface          $uuidGenerator,
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
                $user = AuthUser::create(
                    new AuthUserId($this->uuidGenerator->generate()),
                    $email,
                    $provider,
                );

                $this->userRepository->save($user);
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
