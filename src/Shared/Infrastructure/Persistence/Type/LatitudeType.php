<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Type;

use App\Shared\Domain\ValueObject\Latitude;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LatitudeType extends Type
{
    public const string NAME = 'latitude';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getFloatDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?float
    {
        return $value instanceof Latitude ? $value->value() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Latitude
    {
        return $value !== null ? new Latitude((float) $value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
