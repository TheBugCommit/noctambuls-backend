<?php

declare(strict_types=1);

namespace App\Census\Application\PublishCensus;

readonly class PublishCensusRequest
{
    public function __construct(public string $id)
    {
    }
}
