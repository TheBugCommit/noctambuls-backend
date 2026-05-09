<?php

declare(strict_types=1);

namespace App\Auth\Application\ListAppUsers;

final readonly class ListAppUsersResponse
{
    public function __construct(
        public array $users,
        public int   $total,
        public int   $page,
        public int   $limit,
    ) {
    }
}
