<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum SkyCondition: int
{
    case Clear = 0;
    case PartialCloudy = 1;
    case Overcast = 2;
}
