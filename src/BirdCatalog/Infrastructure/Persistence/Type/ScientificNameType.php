<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence\Type;

use App\BirdCatalog\Domain\ValueObject\ScientificName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ScientificNameType extends StringType
{
    public const string NAME = 'scientific_name_vo';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ScientificName
    {
        if ($value === null) {
            return null;
        }

        return new ScientificName((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof ScientificName ? $value->value() : (string) $value;
    }
}
