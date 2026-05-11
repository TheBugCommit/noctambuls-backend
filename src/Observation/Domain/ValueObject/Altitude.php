<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

use App\Shared\Domain\ValueObject\FloatValueObject;

final class Altitude extends FloatValueObject
{

    public function __construct(float $value)
    {
        parent::__construct($value);

        if ($value < -500 || $value > 12000) {
            throw new \InvalidArgumentException(
                'Altitude must be between -500 and 12000 meters.'
            );
        }
    }
}
