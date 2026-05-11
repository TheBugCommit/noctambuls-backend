<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp;

readonly class SyncUpRequest
{
    public function __construct(
        public string $annotatorId,
        public array $sessions,
    ) {
    }
}
