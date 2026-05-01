<?php

declare(strict_types=1);

namespace App\Auth\Application\SignUp;

readonly class SignUpResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
    ) {
    }
}
