<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Jwt;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use Firebase\JWT\JWT;

final class FirebaseJwtTokenGenerator implements JwtTokenGeneratorInterface
{
    private const string ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $jwtSecret,
        private readonly int $ttl,
    ) {
    }

    public function generate(AuthUser $user): string
    {
        $now = time();

        return JWT::encode([
            'sub' => $user->id()->value(),
            'email' => $user->email()->value(),
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ], $this->jwtSecret, self::ALGORITHM);
    }
}
