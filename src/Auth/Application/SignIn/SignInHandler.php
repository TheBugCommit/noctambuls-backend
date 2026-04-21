<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Entity\RefreshToken;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\RefreshTokenId;
use App\Auth\Domain\ValueObject\TokenString;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Random\RandomException;
use Throwable;

final readonly class SignInHandler
{
    public function __construct(
        private ExternalTokenValidatorInterface $tokenValidator,
        private AuthUserRepositoryInterface     $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private TransactionManagerInterface     $transactionManager,
        private UuidGeneratorInterface          $uuidGenerator,
    ) {
    }

    /**
     * @throws Throwable
     * @throws RandomException
     */
    public function handle(SignInRequest $request): SignInResponse
    {
        $this->transactionManager->begin();

        try {
            $email = $this->tokenValidator->validate($request->externalToken);
            $provider = new Provider($request->providerType);

            $user = $this->userRepository->findByEmailAndProvider($email, $provider);

            if ($user === null) {
                $user = AuthUser::create(
                    new AuthUserId($this->uuidGenerator->generate()),
                    $email,
                    $provider
                );

                $this->userRepository->save($user);
            }

            $tokenStringVo = TokenString::generate();
            $refreshToken = RefreshToken::create(
                new RefreshTokenId($this->uuidGenerator->generate()),
                $tokenStringVo,
                $user->id(),
            );

            $this->refreshTokenRepository->save($refreshToken);

            $response = new SignInResponse(
                $this->jwtGenerator->generate($user),
                $tokenStringVo->value()
            );

            $this->transactionManager->commit();

            return $response;

        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
