<?php

declare(strict_types=1);

namespace App\Observation\Application\UpdateSession;

final readonly class UpdateSessionRequest
{
    public function __construct(
        public string $sessionId,
        public int $temperature,
        public int $wind,
        public int $sky,
        public int $rain,
        public int $visibility,
        public int $moon,
        public ?int $listeningPoint,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $accuracy = null,
        public ?int $locationSource = null,
    ) {
    }
}
