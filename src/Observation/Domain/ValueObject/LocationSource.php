<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum LocationSource: int
{
    case GPS = 0;
    case Manual = 1;
}
