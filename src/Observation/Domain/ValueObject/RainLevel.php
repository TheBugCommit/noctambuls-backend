<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum RainLevel: int
{
    case None = 0;
    case Drizzle = 1;
    case Storm = 2;
}
