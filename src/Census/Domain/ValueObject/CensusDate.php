<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusDateException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;

final class CensusDate extends DateTimeValueObject
{
    public function __construct(readonly string $date)
    {
        parent::__construct($date);
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw new InvalidCensusDateException($date);
        }

        $this->value = $parsed->setTime(0, 0, 0);
    }
}
