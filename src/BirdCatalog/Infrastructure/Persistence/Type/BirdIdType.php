<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence\Type;

use App\BirdCatalog\Domain\ValueObject\BirdId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class BirdIdType extends StringType
{
    public const string NAME = 'bird_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?BirdId
    {
        if ($value === null) {
            return null;
        }

        return new BirdId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof BirdId ? $value->value() : (string) $value;
    }
}
