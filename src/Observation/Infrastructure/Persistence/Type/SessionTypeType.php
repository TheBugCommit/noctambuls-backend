<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence\Type;

use App\Observation\Domain\ValueObject\SessionType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class SessionTypeType extends Type
{
    public const string NAME = 'session_type';

    public function getName(): string { return self::NAME; }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : SessionType::from((int) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if ($value === null) return null;
        return $value instanceof SessionType ? $value->value : (int) $value;
    }
}
