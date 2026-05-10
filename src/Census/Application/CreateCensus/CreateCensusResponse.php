<?php

declare(strict_types=1);

namespace App\Census\Application\CreateCensus;

readonly class CreateCensusResponse
{
    public function __construct(public string $id)
    {
    }
}
