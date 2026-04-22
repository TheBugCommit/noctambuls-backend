<?php

declare(strict_types=1);

namespace App\Auth\Application\ChangeUserRole;

readonly class ChangeUserRoleRequest
{
    public function __construct(
        public string $userId,
        public string $role,
    ) {
    }
}
