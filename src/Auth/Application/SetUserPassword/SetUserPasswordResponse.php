<?php

declare(strict_types=1);

namespace App\Auth\Application\SetUserPassword;

readonly class SetUserPasswordResponse
{
    public function __construct(
        public string $userId,
    ) {
    }
}
