<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

interface MonthlyObservationSummaryRepositoryInterface
{
    /**
     * @param list<array{speciesId: string, year: int, month: int, count: int}> $rows
     */
    public function upsertBatch(array $rows): void;

    /** @return list<array{species_id: string, species_name: string, year: int, month: int, count: int}> */
    public function findByYear(int $year): array;

    /** @return list<int> */
    public function findAvailableYears(): array;
}
