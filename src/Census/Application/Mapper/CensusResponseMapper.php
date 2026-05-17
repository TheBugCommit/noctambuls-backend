<?php

declare(strict_types=1);

namespace App\Census\Application\Mapper;

use App\Census\Application\CreateCensus\CreateCensusResponse;
use App\Census\Application\GetCensus\GetCensusResponse;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesResponse;
use App\Census\Application\ListCensuses\ListCensusesResponse;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;

final class CensusResponseMapper
{
    public static function toGetCensusResponse(Census $census, string $deepLink, string $qrBase64): GetCensusResponse
    {
        $madrid = self::utcEventToMadrid($census);

        return new GetCensusResponse(
            id: $census->id()->value(),
            title: $census->title()->value(),
            date: $madrid->format('Y-m-d'),
            time: $madrid->format('H:i'),
            latitude: $census->latitude()->value(),
            longitude: $census->longitude()->value(),
            locationName: $census->locationName()->value(),
            description: $census->description()?->value(),
            status: $census->status()->value,
            createdAt: $census->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $census->updatedAt()->format(\DateTimeInterface::ATOM),
            publishedAt: $census->publishedAt()?->format(\DateTimeInterface::ATOM),
            isEditable: $census->isEditable(),
            isDeleted: $census->isDeleted(),
            deepLink: $deepLink,
            qrBase64: $qrBase64,
        );
    }

    /**
     * @param Census[] $censuses
     */
    public static function toPublishedCensusesResponse(array $censuses, string $serverTime): GetPublishedCensusesResponse
    {
        $items = array_map(static fn(Census $census) => self::toPublishedCensusItem($census), $censuses);

        return new GetPublishedCensusesResponse($items, $serverTime);
    }

    private static function toPublishedCensusItem(Census $census): array
    {
        $madrid = self::utcEventToMadrid($census);

        return [
            'id'           => $census->id()->value(),
            'title'        => $census->title()->value(),
            'date'         => $madrid->format('Y-m-d'),
            'time'         => $madrid->format('H:i'),
            'latitude'     => $census->latitude()->value(),
            'longitude'    => $census->longitude()->value(),
            'locationName' => $census->locationName()->value(),
            'description'  => $census->description()?->value(),
            'updatedAt'    => $census->updatedAt()->format(\DateTimeInterface::ATOM),
            'isDeleted'    => $census->isDeleted(),
        ];
    }

    public static function toCreateCensusResponse(CensusId $id): CreateCensusResponse
    {
        return new CreateCensusResponse($id->value());
    }

    /**
     * @param Census[] $censuses
     */
    public static function toListCensusesResponse(array $censuses, int $total): ListCensusesResponse
    {
        $items = array_map(
            static fn(Census $census) => self::toListItem($census),
            $censuses
        );

        return new ListCensusesResponse($items, $total);
    }

    private static function toListItem(Census $census): array
    {
        $madrid = self::utcEventToMadrid($census);

        return [
            'id'           => $census->id()->value(),
            'title'        => $census->title()->value(),
            'date'         => $madrid->format('Y-m-d'),
            'time'         => $madrid->format('H:i'),
            'longitude'    => $census->longitude()->value(),
            'latitude'     => $census->latitude()->value(),
            'locationName' => $census->locationName()->value(),
            'description'  => $census->description()?->value(),
            'status'       => $census->status()->value,
            'createdAt'    => $census->createdAt()->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('d/m/Y H:i'),
            'isEditable'   => $census->isEditable(),
            'isDeleted'    => $census->isDeleted(),
        ];
    }

    private static function utcEventToMadrid(Census $census): \DateTimeImmutable
    {
        return (new \DateTimeImmutable(
            $census->date()->format('Y-m-d') . ' ' . $census->time()->format('H:i'),
            new \DateTimeZone('UTC')
        ))->setTimezone(new \DateTimeZone('Europe/Madrid'));
    }
}
