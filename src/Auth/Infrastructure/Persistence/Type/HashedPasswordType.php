<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\ValueObject\HashedPassword;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class HashedPasswordType extends StringType
{
    public const string NAME = 'hashed_password_vo';

    public function getName(): string { return self::NAME; }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?HashedPassword
    {
        return $value === null ? null : new HashedPassword((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value instanceof HashedPassword ? $value->value() : (string) $value;
    }
}
