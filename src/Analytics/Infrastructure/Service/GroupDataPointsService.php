<?php

namespace App\Analytics\Infrastructure\Service;

class GroupDataPointsService
{
    public function groupDataPoints(array $dataPoints, int $precision) : array {
        $grouped = [];

        foreach ($dataPoints as $point) {
            $lat = round($point->lat, $precision);
            $lon = round($point->lon, $precision);
            $key = "{$point->speciesId}|{$lat}|{$lon}|{$point->year}";

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'speciesId' => $point->speciesId,
                    'lat'       => $lat,
                    'lon'       => $lon,
                    'year'      => $point->year,
                    'count'     => 0,
                ];
            }
            $grouped[$key]['count'] += $point->delta;
        }

        return $grouped;
    }
}
