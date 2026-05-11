<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract class DateTimeValueObject
{
    protected \DateTimeImmutable $value;

    public function __construct(string $date)
    {
    }

    public function value(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format): string
    {
        return $this->value->format($format);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value->format('Y-m-d');
    }
}
