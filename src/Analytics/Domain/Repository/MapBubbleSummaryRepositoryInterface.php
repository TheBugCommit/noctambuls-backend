<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

use DateTimeImmutable;

interface MapBubbleSummaryRepositoryInterface
{
    /**
     * @param list<array{speciesId: string, lat: float, lon: float, year: int, count: int}> $rows
     */
    public function upsertBatch(array $rows): void;

    /** @return list<array<string, mixed>> */
    public function findSince(?DateTimeImmutable $since): array;
}
