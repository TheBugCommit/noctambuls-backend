<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence\Type;

use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class BirdDescriptionType extends TextType
{
    public const string NAME = 'bird_description_vo';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?BirdDescription
    {
        if ($value === null) {
            return null;
        }

        return new BirdDescription((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof BirdDescription ? $value->value() : (string) $value;
    }
}
