<?php

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\ValueObject\TokenString;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class TokenStringType extends StringType
{
    public const string NAME = 'token_string';

    public function getName(): string { return self::NAME; }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?TokenString
    {
        return $value === null ? null : new TokenString((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value instanceof TokenString ? $value->value() : (string) $value;
    }
}
