<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence;

use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

final readonly class DoctrineMapBubbleSummaryRepository implements MapBubbleSummaryRepositoryInterface
{
    public function __construct(private Connection $connection) {}

    public function upsertBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($rows), '(?, ?, ?, ?, ?, UTC_TIMESTAMP())'));
        $sql = "INSERT INTO map_bubble_summary (species_id, lat, lon, year, count, updated_at)
                VALUES {$placeholders}
                ON DUPLICATE KEY UPDATE count = GREATEST(0, count + VALUES(count)), updated_at = UTC_TIMESTAMP()";

        $params = [];
        foreach ($rows as $row) {
            $params[] = $row['speciesId'];
            $params[] = $row['lat'];
            $params[] = $row['lon'];
            $params[] = $row['year'];
            $params[] = $row['count'];
        }

        $this->connection->executeStatement($sql, $params);
    }

    public function findSince(?DateTimeImmutable $since): array
    {
        $orderClause = 'ORDER BY updated_at ASC';

        if ($since !== null) {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT species_id, lat, lon, year, count,
                        DATE_FORMAT(updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
                 FROM map_bubble_summary
                 WHERE updated_at >= :since
                 {$orderClause}",
                ['since' => $since->format('Y-m-d H:i:s')],
            );
        } else {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT species_id, lat, lon, year, count,
                        DATE_FORMAT(updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
                 FROM map_bubble_summary
                 {$orderClause}",
            );
        }

        return array_map($this->castRow(...), $rows);
    }

    private function castRow(array $row): array
    {
        return [
            'species_id' => $row['species_id'],
            'lat'        => (float) $row['lat'],
            'lon'        => (float) $row['lon'],
            'year'       => (int)   $row['year'],
            'count'      => (int)   $row['count'],
            'updated_at' => $row['updated_at'],
        ];
    }
}
