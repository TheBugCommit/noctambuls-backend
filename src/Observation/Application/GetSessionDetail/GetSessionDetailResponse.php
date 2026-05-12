<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionDetail;

final readonly class GetSessionDetailResponse
{
    public function __construct(
        public array $session,
    ) {
    }
}
