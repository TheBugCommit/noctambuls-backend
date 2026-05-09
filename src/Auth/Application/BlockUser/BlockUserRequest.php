<?php

declare(strict_types=1);

namespace App\Auth\Application\BlockUser;

final readonly class BlockUserRequest
{
    public function __construct(public string $userId)
    {
    }
}
