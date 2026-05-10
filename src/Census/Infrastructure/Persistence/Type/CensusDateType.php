<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence\Type;

use App\Census\Domain\ValueObject\CensusDate;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CensusDateType extends Type
{
    public const string NAME = 'census_date';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CensusDate
    {
        if ($value === null || $value instanceof CensusDate) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return new CensusDate($value->format('Y-m-d'));
        }

        return new CensusDate((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CensusDate) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
