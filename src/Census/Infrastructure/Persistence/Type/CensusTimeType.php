<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence\Type;

use App\Census\Domain\ValueObject\CensusTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CensusTimeType extends Type
{
    public const string NAME = 'census_time';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getTimeTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CensusTime
    {
        if ($value === null || $value instanceof CensusTime) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return new CensusTime($value->format('H:i'));
        }

        $timeStr = (string) $value;

        if (strlen($timeStr) >= 8) {
            $timeStr = substr($timeStr, 0, 5);
        }

        return new CensusTime($timeStr);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CensusTime) {
            return $value->format('H:i:s');
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
