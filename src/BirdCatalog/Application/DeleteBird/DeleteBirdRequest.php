<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\DeleteBird;

readonly class DeleteBirdRequest
{
    public function __construct(
        public string $id,
    ) {
    }
}
