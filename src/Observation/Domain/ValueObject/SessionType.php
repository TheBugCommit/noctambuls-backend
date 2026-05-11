<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum SessionType: int
{
    case Incidental = 0;
    case Census = 1;
    case Station = 2;
}
