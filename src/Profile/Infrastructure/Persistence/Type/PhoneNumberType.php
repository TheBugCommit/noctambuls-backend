<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence\Type;

use App\Profile\Domain\ValueObject\PhoneNumber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PhoneNumberType extends StringType
{
    public const string NAME = 'phone_number';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PhoneNumber
    {
        if ($value === null) {
            return null;
        }

        return new PhoneNumber((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof PhoneNumber ? $value->value() : (string) $value;
    }
}
