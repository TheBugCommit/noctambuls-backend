<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionDetail;

final readonly class GetSessionDetailRequest
{
    public function __construct(
        public string $sessionId,
    ) {
    }
}
