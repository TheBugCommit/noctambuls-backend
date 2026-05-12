<?php

declare(strict_types=1);

namespace App\Observation\Application\SoftDeleteSession;

final readonly class SoftDeleteSessionRequest
{
    public function __construct(
        public string $sessionId,
    ) {
    }
}
