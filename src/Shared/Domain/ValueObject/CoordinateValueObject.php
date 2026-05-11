<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidCoordinateException;

abstract class CoordinateValueObject
{
    protected float $value;

    public function __construct(mixed $value)
    {
        if ($value === null || $value === '') {
            $this->throwEmptyException();
        }

        if (!is_numeric($value)) {
            throw InvalidCoordinateException::invalidType(get_debug_type($value));
        }

        $floatValue = (float) $value;
        $this->validate($floatValue);

        $this->value = $floatValue;
    }

    public function value(): float
    {
        return $this->value;
    }

    abstract protected function validate(float $value): void;
    abstract protected function throwEmptyException(): void;
}
