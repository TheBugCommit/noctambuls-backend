<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum TemperatureRange: int
{
    case BelowZero = 0;
    case ZeroToTen = 1;
    case TenToTwenty = 2;
    case TwentyToThirty = 3;
    case AboveThirty = 4;
}
