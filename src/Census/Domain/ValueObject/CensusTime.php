<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusTimeException;

final class CensusTime
{
    private \DateTimeImmutable $value;

    public function __construct(string $time)
    {
        $parsed = \DateTimeImmutable::createFromFormat('H:i', $time);

        if (!$parsed || $parsed->format('H:i') !== $time) {
            throw new InvalidCensusTimeException($time);
        }

        $this->value = $parsed;
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
