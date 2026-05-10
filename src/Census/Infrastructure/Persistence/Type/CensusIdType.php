<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence\Type;

use App\Census\Domain\ValueObject\CensusId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CensusIdType extends StringType
{
    public const string NAME = 'census_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CensusId
    {
        if ($value === null) {
            return null;
        }

        return new CensusId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof CensusId ? $value->value() : (string) $value;
    }
}
