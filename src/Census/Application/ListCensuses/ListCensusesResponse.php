<?php

declare(strict_types=1);

namespace App\Census\Application\ListCensuses;

readonly class ListCensusesResponse
{
    public function __construct(
        public array $censuses,
        public int $total,
    ) {
    }
}
