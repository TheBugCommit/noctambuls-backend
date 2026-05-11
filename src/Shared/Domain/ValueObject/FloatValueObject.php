<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract class FloatValueObject
{
    public function __construct(private readonly float $value)
    {
        if (is_nan($value) || is_infinite($value)) {
            throw new \InvalidArgumentException('Must be a finite number.');
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
