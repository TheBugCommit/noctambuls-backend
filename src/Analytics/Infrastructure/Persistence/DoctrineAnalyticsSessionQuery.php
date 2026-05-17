<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence;

use App\Analytics\Domain\Event\ObservationDataPoint;
use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

final readonly class DoctrineAnalyticsSessionQuery implements AnalyticsSessionQueryInterface
{
    public function __construct(private Connection $connection) {}

    public function findDataPointsSince(DateTimeImmutable $since): array
    {
        $sinceFormatted = $since->format('Y-m-d H:i:s');

        $sql = <<<SQL
            -- Arm 1: new active observations created since last run
            SELECT o.species_id,
                   COALESCE(o.latitude,  s.latitude)  AS lat,
                   COALESCE(o.longitude, s.longitude) AS lon,
                   YEAR(COALESCE(s.start_time, s.created_at))  AS obs_year,
                   MONTH(COALESCE(s.start_time, s.created_at)) AS obs_month,
                   1 AS delta
            FROM sessions s
            JOIN observations o ON o.session_id = s.id
            WHERE o.created_at >= :since
              AND s.is_deleted  = 0
              AND o.is_deleted  = 0
              AND COALESCE(o.latitude,  s.latitude)  IS NOT NULL
              AND COALESCE(o.longitude, s.longitude) IS NOT NULL

            UNION ALL

            -- Arm 2: previously-counted observations now soft-deleted since last run
            SELECT o.species_id,
                   COALESCE(o.latitude,  s.latitude)  AS lat,
                   COALESCE(o.longitude, s.longitude) AS lon,
                   YEAR(COALESCE(s.start_time, s.created_at))  AS obs_year,
                   MONTH(COALESCE(s.start_time, s.created_at)) AS obs_month,
                   -1 AS delta
            FROM sessions s
            JOIN observations o ON o.session_id = s.id
            WHERE COALESCE(o.deleted_at, o.updated_at) >= :since
              AND o.is_deleted  = 1
              AND o.created_at  < :since
              AND COALESCE(o.latitude,  s.latitude)  IS NOT NULL
              AND COALESCE(o.longitude, s.longitude) IS NOT NULL

            UNION ALL

            -- Arm 3: observations restored since last run whose subtract was already applied
            SELECT o.species_id,
                   COALESCE(o.latitude,  s.latitude)  AS lat,
                   COALESCE(o.longitude, s.longitude) AS lon,
                   YEAR(COALESCE(s.start_time, s.created_at))  AS obs_year,
                   MONTH(COALESCE(s.start_time, s.created_at)) AS obs_month,
                   1 AS delta
            FROM sessions s
            JOIN observations o ON o.session_id = s.id
            WHERE o.updated_at  >= :since
              AND o.is_deleted   = 0
              AND o.created_at   < :since
              AND o.deleted_at  IS NOT NULL
              AND o.deleted_at   < :since
              AND COALESCE(o.latitude,  s.latitude)  IS NOT NULL
              AND COALESCE(o.longitude, s.longitude) IS NOT NULL
        SQL;

        $rows = $this->connection->fetchAllAssociative($sql, ['since' => $sinceFormatted]);

        return array_map(
            static fn(array $row) => new ObservationDataPoint(
                speciesId: $row['species_id'],
                lat:       (float) $row['lat'],
                lon:       (float) $row['lon'],
                year:      (int)   $row['obs_year'],
                month:     (int)   $row['obs_month'],
                delta:     (int)   $row['delta'],
            ),
            $rows,
        );
    }

    public function findDeletedDataPoints(DateTimeImmutable $countedBefore): array
    {
        $sql = <<<SQL
            SELECT o.species_id,
                   COALESCE(o.latitude,  s.latitude)  AS lat,
                   COALESCE(o.longitude, s.longitude) AS lon,
                   YEAR(COALESCE(s.start_time, s.created_at))  AS obs_year,
                   MONTH(COALESCE(s.start_time, s.created_at)) AS obs_month
            FROM sessions s
            JOIN observations o ON o.session_id = s.id
            WHERE s.is_deleted = 1
              AND o.created_at < :countedBefore
              AND COALESCE(o.latitude,  s.latitude)  IS NOT NULL
              AND COALESCE(o.longitude, s.longitude) IS NOT NULL
        SQL;

        $rows = $this->connection->fetchAllAssociative($sql, [
            'countedBefore' => $countedBefore->format('Y-m-d H:i:s'),
        ]);

        return array_map(
            static fn(array $row) => new ObservationDataPoint(
                speciesId: $row['species_id'],
                lat:       (float) $row['lat'],
                lon:       (float) $row['lon'],
                year:      (int)   $row['obs_year'],
                month:     (int)   $row['obs_month'],
                delta:     -1,
            ),
            $rows,
        );
    }
}
