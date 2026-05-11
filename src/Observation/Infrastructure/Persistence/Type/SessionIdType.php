<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence\Type;

use App\Observation\Domain\ValueObject\SessionId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class SessionIdType extends StringType
{
    public const string NAME = 'session_id';

    public function getName(): string { return self::NAME; }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value === null ? null : new SessionId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) return null;
        return $value instanceof SessionId ? $value->value() : (string) $value;
    }
}
