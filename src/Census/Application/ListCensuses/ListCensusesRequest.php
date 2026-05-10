<?php

declare(strict_types=1);

namespace App\Census\Application\ListCensuses;

readonly class ListCensusesRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 25,
    ) {
    }
}
