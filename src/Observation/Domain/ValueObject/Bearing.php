<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

use App\Shared\Domain\ValueObject\FloatValueObject;

final class Bearing extends FloatValueObject
{
    public function __construct(float $value)
    {
        if ($value < -360.0 || $value > 360.0) {
            throw new \InvalidArgumentException(
                "Bearing reading is invalid $value. It must be between -360 and 360 degrees.");
        }

        $normalized = fmod($value, 360.0);

        if ($normalized < 0) {
            $normalized += 360.0;
        }

        parent::__construct($normalized);
    }
}
