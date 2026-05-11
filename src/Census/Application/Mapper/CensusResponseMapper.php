<?php

declare(strict_types=1);

namespace App\Census\Application\Mapper;

use App\Census\Application\CreateCensus\CreateCensusResponse;
use App\Census\Application\GetCensus\GetCensusResponse;
use App\Census\Application\ListCensuses\ListCensusesResponse;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;

final class CensusResponseMapper
{
    public static function toGetCensusResponse(Census $census): GetCensusResponse
    {
        return new GetCensusResponse(
            id: $census->id()->value(),
            title: $census->title()->value(),
            date: $census->date()->format('Y-m-d'),
            time: $census->time()->format('H:i'),
            latitude: $census->latitude()->value(),
            longitude: $census->longitude()->value(),
            locationName: $census->locationName()->value(),
            description: $census->description()?->value(),
            status: $census->status()->value,
            createdAt: $census->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $census->updatedAt()->format(\DateTimeInterface::ATOM),
            publishedAt: $census->publishedAt()?->format(\DateTimeInterface::ATOM),
            isEditable: $census->isEditable(),
        );
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
        return [
            'id'           => $census->id()->value(),
            'title'        => $census->title()->value(),
            'date'         => $census->date()->format('Y-m-d'),
            'time'         => $census->time()->format('H:i'),
            'longitude'    => $census->longitude()->value(),
            'latitude'     => $census->latitude()->value(),
            'locationName' => $census->locationName()->value(),
            'description'  => $census->description()?->value(),
            'status'       => $census->status()->value,
            'createdAt'    => $census->createdAt()->format('d/m/Y H:i'),
            'isEditable'   => $census->isEditable(),
        ];
    }
}
