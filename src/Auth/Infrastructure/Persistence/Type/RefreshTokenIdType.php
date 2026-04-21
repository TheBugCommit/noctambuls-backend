<?php

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\ValueObject\RefreshTokenId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class RefreshTokenIdType extends StringType
{
    public const string NAME = 'refresh_token_id';

    public function getName(): string { return self::NAME; }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?RefreshTokenId
    {
        return $value === null ? null : new RefreshTokenId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value instanceof RefreshTokenId ? $value->value() : (string) $value;
    }
}
