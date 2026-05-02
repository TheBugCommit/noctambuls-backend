<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence\Type;

use App\Profile\Domain\ValueObject\OrnithologistCode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class OrnithologistCodeType extends StringType
{
    public const string NAME = 'ornithologist_code';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?OrnithologistCode
    {
        if ($value === null) {
            return null;
        }

        return new OrnithologistCode((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof OrnithologistCode ? $value->value() : (string) $value;
    }
}
