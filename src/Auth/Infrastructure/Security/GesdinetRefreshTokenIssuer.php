<?php

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\SignIn\RefreshTokenIssuerInterface;
use App\Auth\Domain\Entity\AuthUser;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

final readonly class GesdinetRefreshTokenIssuer implements RefreshTokenIssuerInterface
{
    public function __construct(
        private RefreshTokenGeneratorInterface $generator,
        private RefreshTokenManagerInterface $manager,
        private int $refreshTokenTtl,
    ) {}

    public function issueTokenForUser(AuthUser $user): string
    {
        $userAdapter = new AuthUserSymfonyAdapter($user);

        $refreshToken = $this->generator->createForUserWithTtl($userAdapter, $this->refreshTokenTtl);
        $this->manager->save($refreshToken);

        return $refreshToken->getRefreshToken();
    }
}
