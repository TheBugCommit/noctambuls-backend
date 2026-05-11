<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use InvalidArgumentException;

class InvalidCoordinateException extends InvalidArgumentException
{
    public static function invalidType(string $type): self
    {
        return new self(sprintf('Coordinate must be numeric, "%s" given.', $type));
    }

    public static function invalidLatitude(float $value): self
    {
        return new self(sprintf('Invalid latitude: %f. Must be between -90 and 90.', $value));
    }

    public static function invalidLongitude(float $value): self
    {
        return new self(sprintf('Invalid longitude: %f. Must be between -180 and 180.', $value));
    }
}
