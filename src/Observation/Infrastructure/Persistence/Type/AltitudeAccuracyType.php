<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence\Type;

use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class AltitudeAccuracyType extends Type
{
    public const string NAME = 'altitude_accuracy';

    public function getName(): string { return self::NAME; }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getFloatDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : new AltitudeAccuracy((float) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?float
    {
        if ($value === null) return null;
        return $value instanceof AltitudeAccuracy ? $value->value() : (float) $value;
    }
}
