<?php

declare(strict_types=1);

namespace App\Auth\Application\DeleteBackofficeUser;

final readonly class DeleteBackofficeUserResponse
{
    public function __construct(public string $userId)
    {
    }
}
