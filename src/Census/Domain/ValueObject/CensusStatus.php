<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

enum CensusStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    public function isDraft(): bool {
        return $this === self::DRAFT;
    }

    public function isPublished(): bool {
        return $this === self::PUBLISHED;
    }
}
