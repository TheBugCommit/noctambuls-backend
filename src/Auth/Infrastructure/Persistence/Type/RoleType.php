<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\ValueObject\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class RoleType extends StringType
{
    public const string NAME = 'role_vo';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Role
    {
        if ($value === null) {
            return null;
        }

        return Role::from((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Role ? $value->value : (string) $value;
    }
}
