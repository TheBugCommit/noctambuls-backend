<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence;

use App\Analytics\Domain\Repository\MonthlyObservationSummaryRepositoryInterface;
use Doctrine\DBAL\Connection;

final readonly class DoctrineMonthlyObservationSummaryRepository implements MonthlyObservationSummaryRepositoryInterface
{
    public function __construct(private Connection $connection) {}

    public function upsertBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($rows), '(?, ?, ?, ?, UTC_TIMESTAMP())'));
        $sql = "INSERT INTO monthly_observation_summary (species_id, year, month, count, updated_at)
                VALUES {$placeholders}
                ON DUPLICATE KEY UPDATE count = GREATEST(0, count + VALUES(count)), updated_at = UTC_TIMESTAMP()";

        $params = [];
        foreach ($rows as $row) {
            $params[] = $row['speciesId'];
            $params[] = $row['year'];
            $params[] = $row['month'];
            $params[] = $row['count'];
        }

        $this->connection->executeStatement($sql, $params);
    }

    public function findByYear(int $year): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT m.species_id,
                    COALESCE(bt.common_name, b.scientific_name) AS species_name,
                    m.year,
                    m.month,
                    m.count
             FROM monthly_observation_summary m
             JOIN birds b ON b.id = m.species_id
             LEFT JOIN bird_translations bt ON bt.bird_id = m.species_id AND bt.language_code = 'ca'
             WHERE m.year = :year
             ORDER BY species_name ASC, m.month ASC",
            ['year' => $year],
        );

        return array_map(
            static fn(array $row) => [
                'species_id'   => $row['species_id'],
                'species_name' => $row['species_name'],
                'year'         => (int) $row['year'],
                'month'        => (int) $row['month'],
                'count'        => (int) $row['count'],
            ],
            $rows,
        );
    }

    public function findAvailableYears(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT DISTINCT year FROM monthly_observation_summary ORDER BY year DESC',
        );

        return array_map(static fn(array $row) => (int) $row['year'], $rows);
    }
}
