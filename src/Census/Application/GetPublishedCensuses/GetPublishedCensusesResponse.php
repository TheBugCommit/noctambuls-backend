<?php

declare(strict_types=1);

namespace App\Census\Application\GetPublishedCensuses;

readonly class GetPublishedCensusesResponse
{
    public function __construct(
        public array  $censuses,
        public string $serverTime,
    ) {}
}
