<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

readonly class SignInResponse
{
    public function __construct(
        public string $token,
        public string $refreshToken,
        public string $tokenType = 'Bearer'
    ) {
    }
}
