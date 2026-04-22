<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Jwt;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final readonly class LexikJwtTokenGenerator implements JwtTokenGeneratorInterface
{
    public function __construct(private JWTTokenManagerInterface $jwtManager)
    {
    }

    public function generate(AuthUser $user): string
    {
        return $this->jwtManager->create(new AuthUserSymfonyAdapter($user));
    }
}
