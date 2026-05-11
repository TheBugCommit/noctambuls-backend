<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence\Type;

use App\Observation\Domain\ValueObject\ListeningPoint;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ListeningPointType extends Type
{
    public const string NAME = 'listening_point';

    public function getName(): string { return self::NAME; }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : new ListeningPoint((int) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) return null;
        return $value instanceof ListeningPoint ? $value->value() : (int) $value;
    }
}
