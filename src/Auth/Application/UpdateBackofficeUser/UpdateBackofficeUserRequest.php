<?php

declare(strict_types=1);

namespace App\Auth\Application\UpdateBackofficeUser;

final readonly class UpdateBackofficeUserRequest
{
    public function __construct(
        public string  $userId,
        public ?string $email = null,
        public ?string $plainPassword = null,
        public ?string $role = null,
    ) {
    }
}
