<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence\Type;

use App\Profile\Domain\ValueObject\ProfileId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProfileIdType extends StringType
{
    public const string NAME = 'profile_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ProfileId
    {
        if ($value === null) {
            return null;
        }

        return new ProfileId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof ProfileId ? $value->value() : (string) $value;
    }
}
