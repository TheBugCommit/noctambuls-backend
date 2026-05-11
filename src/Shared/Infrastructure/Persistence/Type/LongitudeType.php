<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Type;

use App\Shared\Domain\ValueObject\Longitude;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LongitudeType extends Type
{
    public const string NAME = 'longitude';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getFloatDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?float
    {
        return $value instanceof Longitude ? $value->value() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Longitude
    {
        return $value !== null ? new Longitude((float) $value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
