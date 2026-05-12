<?php

declare(strict_types=1);

namespace App\Observation\Application\GetDeletedSessions;

final readonly class GetDeletedSessionsRequest
{
    public function __construct(
        public int $page,
        public int $limit,
    ) {
    }
}
