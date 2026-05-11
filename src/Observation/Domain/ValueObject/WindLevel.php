<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum WindLevel: int
{
    case Calm = 0;
    case Breeze = 1;
    case Moderate = 2;
}
