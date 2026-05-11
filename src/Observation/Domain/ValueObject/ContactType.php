<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

enum ContactType: int
{
    case Visual = 0;
    case Auditive = 1;
}
