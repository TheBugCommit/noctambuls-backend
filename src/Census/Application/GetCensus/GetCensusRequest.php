<?php

declare(strict_types=1);

namespace App\Census\Application\GetCensus;

readonly class GetCensusRequest
{
    public function __construct(public string $id)
    {
    }
}
