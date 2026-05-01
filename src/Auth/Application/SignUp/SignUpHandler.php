<?php

declare(strict_types=1);

namespace App\Auth\Application\SignUp;

use App\Auth\Application\SignIn\RefreshTokenIssuerInterface;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Provider;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Throwable;

final readonly class SignUpHandler
{
    public function __construct(
        private ExternalTokenValidatorInterface $tokenValidator,
        private AuthUserRepositoryInterface     $userRepository,
        private ProfileRepositoryInterface      $profileRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private RefreshTokenIssuerInterface     $refreshTokenIssuer,
        private TransactionManagerInterface     $transactionManager,
        private UuidGeneratorInterface          $uuidGenerator,
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

        $profile = Profile::create(
            new ProfileId($uuid),
            new ProfileName($request->name),
            new PhoneNumber($request->phone),
        );

        $this->transactionManager->begin();
        try {
            $this->userRepository->save($user);
            $this->profileRepository->save($profile);

            $accessToken = $this->jwtGenerator->generate($user);
            $refreshToken = $this->refreshTokenIssuer->issueTokenForUser($user);

            $response = new SignUpResponse($accessToken, $refreshToken);

            $this->transactionManager->commit();

            return $response;
        } catch (Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
