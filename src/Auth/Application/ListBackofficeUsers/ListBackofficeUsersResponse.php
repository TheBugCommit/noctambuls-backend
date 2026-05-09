<?php

declare(strict_types=1);

namespace App\Auth\Application\ListBackofficeUsers;

final readonly class ListBackofficeUsersResponse
{
    public function __construct(public array $users)
    {
    }
}
