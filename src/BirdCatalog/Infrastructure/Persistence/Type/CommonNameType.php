<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence\Type;

use App\BirdCatalog\Domain\ValueObject\CommonName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CommonNameType extends StringType
{
    public const string NAME = 'common_name_vo';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CommonName
    {
        if ($value === null) {
            return null;
        }

        return new CommonName((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof CommonName ? $value->value() : (string) $value;
    }
}
