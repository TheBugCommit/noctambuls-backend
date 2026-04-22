<?php

declare(strict_types=1);

namespace App\Auth\Application\SetUserPassword;

readonly class SetUserPasswordRequest
{
    public function __construct(
        public string $userId,
        public string $plainPassword,
    ) {
    }
}
