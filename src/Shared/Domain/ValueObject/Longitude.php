<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;

class Longitude extends CoordinateValueObject
{
    protected function validate(float $value): void
    {
        if ($value < -180.0 || $value > 180.0) {
            throw InvalidCoordinateException::invalidLongitude($value);
        }
    }

    protected function throwEmptyException(): void
    {
        throw EmptyCoordinateException::forLongitude();
    }
}
