<?php

declare(strict_types=1);

namespace App\Census\Application\DeleteCensus;

readonly class DeleteCensusRequest
{
    public function __construct(public string $id)
    {
    }
}
