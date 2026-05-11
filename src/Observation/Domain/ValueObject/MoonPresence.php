<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum MoonPresence: int
{
    case Absent = 0;
    case Present = 1;
}
