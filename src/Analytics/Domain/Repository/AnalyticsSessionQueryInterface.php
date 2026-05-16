<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

use App\Analytics\Domain\Event\ObservationDataPoint;
use DateTimeImmutable;

interface AnalyticsSessionQueryInterface
{
    /** @return list<ObservationDataPoint> */
    public function findDataPointsSince(DateTimeImmutable $since): array;

    /**
     * @return list<ObservationDataPoint>
     */
    public function findDeletedDataPoints(DateTimeImmutable $countedBefore): array;
}
