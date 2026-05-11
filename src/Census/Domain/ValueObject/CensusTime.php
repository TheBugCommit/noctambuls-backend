<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusTimeException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;

final class CensusTime extends DateTimeValueObject
{
    public function __construct(readonly string $time)
    {
        parent::__construct($time);
        $parsed = \DateTimeImmutable::createFromFormat('H:i', $time);

        if (!$parsed || $parsed->format('H:i') !== $time) {
            throw new InvalidCensusTimeException($time);
        }

        $this->value = $parsed;
    }
}
