<?php

declare(strict_types=1);

namespace App\Auth\Application\UnblockUser;

final readonly class UnblockUserResponse
{
    public function __construct(public string $userId)
    {
    }
}
