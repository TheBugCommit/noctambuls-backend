<?php

declare(strict_types=1);

namespace App\Auth\Application\CreateBackofficeUser;

final readonly class CreateBackofficeUserResponse
{
    public function __construct(public string $userId)
    {
    }
}
