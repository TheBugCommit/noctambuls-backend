<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence\Type;

use App\Census\Domain\ValueObject\CensusDescription;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class CensusDescriptionType extends TextType
{
    public const string NAME = 'census_description';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CensusDescription
    {
        if ($value === null) {
            return null;
        }

        return new CensusDescription((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof CensusDescription ? $value->value() : (string) $value;
    }
}
