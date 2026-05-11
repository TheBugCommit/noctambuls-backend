<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum VisibilityLevel: int
{
    case Poor = 0;
    case Moderate = 1;
    case Good = 2;
}
