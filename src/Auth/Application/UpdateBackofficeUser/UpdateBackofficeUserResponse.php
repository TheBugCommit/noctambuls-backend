<?php

declare(strict_types=1);

namespace App\Auth\Application\UpdateBackofficeUser;

final readonly class UpdateBackofficeUserResponse
{
    public function __construct(public string $userId)
    {
    }
}
