<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;

class Latitude extends CoordinateValueObject
{
    protected function validate(float $value): void
    {
        if ($value < -90.0 || $value > 90.0) {
            throw InvalidCoordinateException::invalidLatitude($value);
        }
    }

    protected function throwEmptyException(): void
    {
        throw EmptyCoordinateException::forLatitude();
    }
}
