<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp;

readonly class SyncUpResponse
{
    public function __construct(
        public array $synced,
        public array $failed,
    ) {
    }
}
