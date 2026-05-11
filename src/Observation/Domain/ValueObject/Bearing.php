<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

use App\Shared\Domain\ValueObject\FloatValueObject;

final class Bearing extends FloatValueObject
{
    public function __construct(float $value)
    {
        parent::__construct($value);

        if ($value < 0 || $value >= 360) {
            throw new \InvalidArgumentException(
                'Bearing must be between 0 (inclusive) and 360 (exclusive).'
            );
        }
    }
}
