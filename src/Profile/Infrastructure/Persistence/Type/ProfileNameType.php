<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence\Type;

use App\Profile\Domain\ValueObject\ProfileName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProfileNameType extends StringType
{
    public const string NAME = 'profile_name';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ProfileName
    {
        if ($value === null) {
            return null;
        }

        return new ProfileName((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof ProfileName ? $value->value() : (string) $value;
    }
}
