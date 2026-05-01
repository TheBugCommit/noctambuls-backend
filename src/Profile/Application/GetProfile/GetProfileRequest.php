<?php

declare(strict_types=1);

namespace App\Profile\Application\GetProfile;

readonly class GetProfileRequest
{
    public function __construct(
        public string $id,
    ) {
    }
}
