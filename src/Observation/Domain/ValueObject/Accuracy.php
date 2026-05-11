<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

use App\Shared\Domain\ValueObject\FloatValueObject;

final class Accuracy extends FloatValueObject
{

    public function __construct(float $value)
    {
        parent::__construct($value);

        if ($value < 0 || $value > 10000) {
            throw new \InvalidArgumentException(
                'Altitude accuracy must be between 0 and 10000 meters.'
            );
        }
    }
}
