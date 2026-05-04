<?php

declare(strict_types=1);

namespace App\Auth\Application\AnonymizeUser;

final readonly class AnonymizeUserResponse
{
    public function __construct(public string $userId)
    {
    }
}
