<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\ValueObject\AuthUserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class AuthUserIdType extends StringType
{
    public const string NAME = 'auth_user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?AuthUserId
    {
        if ($value === null) {
            return null;
        }

        return new AuthUserId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof AuthUserId ? $value->value() : (string) $value;
    }
}
