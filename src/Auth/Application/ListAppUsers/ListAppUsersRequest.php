<?php

declare(strict_types=1);

namespace App\Auth\Application\ListAppUsers;

final readonly class ListAppUsersRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
