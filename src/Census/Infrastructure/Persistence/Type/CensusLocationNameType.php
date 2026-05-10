<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence\Type;

use App\Census\Domain\ValueObject\CensusLocationName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CensusLocationNameType extends StringType
{
    public const string NAME = 'census_location_name';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CensusLocationName
    {
        if ($value === null) {
            return null;
        }

        return new CensusLocationName((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof CensusLocationName ? $value->value() : (string) $value;
    }
}
