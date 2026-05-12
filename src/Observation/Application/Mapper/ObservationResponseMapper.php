<?php

declare(strict_types=1);

namespace App\Observation\Application\Mapper;

use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Entity\SessionParticipant;
use Doctrine\Common\Collections\Collection;

final class ObservationResponseMapper
{
    /** @param Session[] $sessions @return array[] */
    public static function toSessionArray(array $sessions): array
    {
        return array_values(array_map(static fn(Session $s) => self::sessionArray($s), $sessions));
    }

    /**
     * @param Observation[]|Collection $observations
     * @return array[]
     */
    public static function toObservationArray(array|Collection $observations): array
    {
        $result = [];
        foreach ($observations as $o) {
            $result[] = [
                'id' => $o->id()->value(),
                'sessionId' => $o->session()->id()->value(),
                'speciesId' => $o->speciesId()->value(),
                'contactType' => $o->contactType()->value,
                'quantity' => $o->quantity()->value(),
                'startTime' => $o->startTime()->format(\DateTimeInterface::ATOM),
                'endTime' => $o->endTime()->format(\DateTimeInterface::ATOM),
                'notes' => $o->notes(),
                'latitude' => $o->latitude()?->value(),
                'longitude' => $o->longitude()?->value(),
                'accuracy' => $o->accuracy()?->value(),
                'altitude' => $o->altitude()?->value(),
                'altitudeAccuracy' => $o->altitudeAccuracy()?->value(),
                'locationSource' => $o->locationSource()?->value,
                'bearing' => $o->bearing()->value(),
                'createdAt' => $o->createdAt()->format(\DateTimeInterface::ATOM),
                'updatedAt' => $o->updatedAt()->format(\DateTimeInterface::ATOM),
                'isDeleted' => $o->isDeleted(),
            ];
        }
        return $result;
    }

    /**
     * @param SessionParticipant[]|Collection $participants
     * @return array[]
     */
    public static function toParticipantArray(array|Collection $participants): array
    {
        $result = [];
        foreach ($participants as $p) {
            $result[] = [
                'sessionId' => $p->session()->id()->value(),
                'ornithologistCode' => $p->ornithologistCode()->value(),
            ];
        }
        return $result;
    }

    /** @param Session[] $sessions @return array[] */
    public static function toNestedSessionArray(array $sessions): array
    {
        $result = [];
        foreach ($sessions as $session) {
            $tmp = self::sessionArray($session);
            $tmp['observations'] = self::toObservationArray($session->observations());
            $tmp['participants'] = self::toParticipantArray($session->participants());
            $result[] = $tmp;
        }
        return $result;
    }

    public static function toMeta(int $total, int $offset, int $limit): array
    {
        $currentPage = $limit > 0 ? (int)floor($offset / $limit) + 1 : 1;
        $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

        return [
            'currentPage' => $currentPage,
            'totalPages' => max(1, $totalPages),
            'totalItems' => $total,
            'hasMore' => ($offset + $limit) < $total,
        ];
    }

    private static function sessionArray(Session $s): array
    {
        return [
            'id' => $s->id()->value(),
            'censusId' => $s->censusId()?->value(),
            'annotatorId' => $s->annotatorId()->value(),
            'sessionType' => $s->sessionType()->value,
            'startTime' => $s->startTime()?->format(\DateTimeInterface::ATOM),
            'endTime' => $s->endTime()?->format(\DateTimeInterface::ATOM),
            'temperature' => $s->temperature()->value,
            'wind' => $s->wind()->value,
            'sky' => $s->sky()->value,
            'rain' => $s->rain()->value,
            'visibility' => $s->visibility()->value,
            'moon' => $s->moon()->value,
            'listeningPoint' => $s->listeningPoint()?->value(),
            'latitude' => $s->latitude()?->value(),
            'longitude' => $s->longitude()?->value(),
            'accuracy' => $s->accuracy()?->value(),
            'altitude' => $s->altitude()?->value(),
            'altitudeAccuracy' => $s->altitudeAccuracy()?->value(),
            'locationSource' => $s->locationSource()?->value,
            'createdAt' => $s->createdAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $s->updatedAt()->format(\DateTimeInterface::ATOM),
            'isDeleted' => $s->isDeleted(),
        ];
    }
}
