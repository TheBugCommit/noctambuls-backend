<?php

declare(strict_types=1);

namespace App\Observation\Application\GetParticipatedSessions;

readonly class GetParticipatedSessionsResponse
{
    public function __construct(
        public array $sessions,
        public array $meta,
    ) {
    }
}
