<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence\Type;

use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class BirdTranslationIdType extends StringType
{
    public const string NAME = 'bird_translation_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?BirdTranslationId
    {
        if ($value === null) {
            return null;
        }

        return new BirdTranslationId((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof BirdTranslationId ? $value->value() : (string) $value;
    }
}
