<?php

declare(strict_types=1);

namespace App\Auth\Application\AnonymizeUser;

final readonly class AnonymizeUserRequest
{
    public function __construct(public string $userId)
    {
    }
}
