<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

enum CensusStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
}
