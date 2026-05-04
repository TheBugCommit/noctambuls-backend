<?php

declare(strict_types=1);

namespace App\Auth\Application\CreateBackofficeUser;

final readonly class CreateBackofficeUserRequest
{
    public function __construct(
        public string $email,
        public string $role,
        public string $plainPassword,
    ) {
    }
}
