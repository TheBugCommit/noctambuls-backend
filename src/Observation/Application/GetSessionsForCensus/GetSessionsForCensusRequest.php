<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForCensus;

final readonly class GetSessionsForCensusRequest
{
    public function __construct(public string $censusId)
    {
    }
}
