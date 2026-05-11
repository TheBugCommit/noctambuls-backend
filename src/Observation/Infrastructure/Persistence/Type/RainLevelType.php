<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence\Type;

use App\Observation\Domain\ValueObject\RainLevel;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class RainLevelType extends Type
{
    public const string NAME = 'rain_level';

    public function getName(): string { return self::NAME; }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : RainLevel::from((int) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) return null;
        return $value instanceof RainLevel ? $value->value : (int) $value;
    }
}
