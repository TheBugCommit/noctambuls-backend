<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusDateException;

final class CensusDate
{
    private \DateTimeImmutable $value;

    public function __construct(string $date)
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw new InvalidCensusDateException($date);
        }

        $this->value = $parsed->setTime(0, 0, 0);
    }

    public function value(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format): string
    {
        return $this->value->format($format);
    }
}
