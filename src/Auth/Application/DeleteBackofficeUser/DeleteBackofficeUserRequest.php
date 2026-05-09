<?php

declare(strict_types=1);

namespace App\Auth\Application\DeleteBackofficeUser;

final readonly class DeleteBackofficeUserRequest
{
    public function __construct(public string $userId)
    {
    }
}
