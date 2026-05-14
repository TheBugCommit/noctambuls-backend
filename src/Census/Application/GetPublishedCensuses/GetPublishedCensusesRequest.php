<?php

declare(strict_types=1);

namespace App\Census\Application\GetPublishedCensuses;

readonly class GetPublishedCensusesRequest
{
    public function __construct(
        public ?\DateTimeImmutable $since = null,
    ) {}
}
