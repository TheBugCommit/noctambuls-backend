<?php

namespace App\Auth\Application\SignIn;

readonly class SignInRequest
{
    public function __construct(
        public string $externalToken,
        public string $providerType
    ) {}
}
