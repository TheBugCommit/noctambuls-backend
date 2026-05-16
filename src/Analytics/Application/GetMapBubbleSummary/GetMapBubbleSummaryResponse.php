<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetMapBubbleSummary;

final readonly class GetMapBubbleSummaryResponse
{
    /** @param list<array<string, mixed>> $items */
    public function __construct(
        public ?string $serverTime,
        public array   $items,
    ) {}
}
