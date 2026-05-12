<?php

declare(strict_types=1);

namespace App\Observation\Application\RestoreSession;

final readonly class RestoreSessionRequest
{
    public function __construct(
        public string $sessionId,
    ) {
    }
}
