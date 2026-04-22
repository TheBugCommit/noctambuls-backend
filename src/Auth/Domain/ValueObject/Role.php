<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

enum Role: string
{
    case USER = 'ROLE_USER';
    case REVIEWER = 'ROLE_REVIEWER';
    case ADMIN = 'ROLE_ADMIN';

    public static function default(): self
    {
        return self::USER;
    }
}
