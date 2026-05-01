<?php

declare(strict_types=1);

namespace App\Auth\Application\SignUp;

readonly class SignUpRequest
{
    public function __construct(
        public string $externalToken,
        public string $providerType,
        public string $name,
        public string $phone,
    ) {
    }
}
