<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use InvalidArgumentException;

class EmptyCoordinateException extends InvalidArgumentException
{
    public static function forLatitude(): self
    {
        return new self('Latitude coordinate cannot be empty.');
    }

    public static function forLongitude(): self
    {
        return new self('Longitude coordinate cannot be empty.');
    }
}
