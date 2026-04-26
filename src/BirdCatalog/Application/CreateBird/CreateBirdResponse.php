<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\CreateBird;

readonly class CreateBirdResponse
{
    public function __construct(
        public string $id,
    ) {
    }
}
