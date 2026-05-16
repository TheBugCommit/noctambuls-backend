<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetMapBubbleSummary;

final readonly class GetMapBubbleSummaryRequest
{
    public function __construct(public ?string $since) {}
}
