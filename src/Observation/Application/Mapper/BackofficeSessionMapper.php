<?php

declare(strict_types=1);

namespace App\Observation\Application\Mapper;

use App\Observation\Domain\Entity\Session;

final class BackofficeSessionMapper
{
    /**
     * @param Session[] $sessions
     */
    public static function toDeletedListArray(array $sessions, array $profileMap, array $censusMap): array
    {
        $result = [];
        foreach ($sessions as $session) {
            $result[] = [
                'id' => $session->id()->value(),
                'annotatorName' => $profileMap[$session->annotatorId()->value()] ?? $session->annotatorId()->value(),
                'sessionType' => $session->sessionType()->value,
                'startTime' => $session->startTime()?->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i'),
                'deletedAt' => $session->updatedAt()->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i'),
                'censusTitle' => $censusMap[$session->censusId()?->value()] ?? null,
            ];
        }
        return $result;
    }

    /**
     * @param Session[] $sessions
     */
    public static function toCensusDetailArray(array $sessions, array $profileMap): array
    {
        $result = [];
        foreach ($sessions as $session) {
            $activeObsCount = 0;
            foreach ($session->observations() as $obs) {
                if (!$obs->isDeleted()) {
                    $activeObsCount++;
                }
            }

            $result[] = [
                'id'               => $session->id()->value(),
                'annotatorName'    => $profileMap[$session->annotatorId()->value()] ?? $session->annotatorId()->value(),
                'sessionType'      => $session->sessionType()->value,
                'startTime'        => $session->startTime()?->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i'),
                'observationCount' => $activeObsCount,
                'listeningPoint'   => $session->listeningPoint()?->value(),
                'latitude'         => $session->latitude()?->value(),
                'longitude'        => $session->longitude()?->value(),
            ];
        }
        return $result;
    }

    /**
     * @param Session[] $sessions
     */
    public static function toActiveListArray(array $sessions, array $profileMap, array $censusMap): array
    {
        $result = [];
        foreach ($sessions as $session) {
            $activeObsCount = 0;
            foreach ($session->observations() as $obs) {
                if (!$obs->isDeleted()) {
                    $activeObsCount++;
                }
            }

            $result[] = [
                'id' => $session->id()->value(),
                'annotatorName' => $profileMap[$session->annotatorId()->value()] ?? $session->annotatorId()->value(),
                'sessionType' => $session->sessionType()->value,
                'startTime' => $session->startTime()?->format('Y-m-d H:i'),
                'observationCount' => $activeObsCount,
                'listeningPoint' => $session->listeningPoint()?->value(),
                'censusTitle' => $censusMap[$session->censusId()?->value()] ?? null,
            ];
        }
        return $result;
    }
}
