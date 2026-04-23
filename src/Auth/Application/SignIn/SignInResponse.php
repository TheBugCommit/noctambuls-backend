<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

readonly class SignInResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
    ) {
    }
}
