<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\GetBird;

readonly class GetBirdRequest
{
    public function __construct(
        public string $id,
    ) {
    }
}
