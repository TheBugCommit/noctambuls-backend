<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence\Type;

use App\Analytics\Domain\ValueObject\CronJobName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class CronJobType extends Type
{
    public const NAME = 'cron_job';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([
            'length' => 255,
        ]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CronJobName
    {
        if ($value === null) {
            return null;
        }

        return CronJobName::from((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if(is_string($value)) {
            return $value;
        }

        if (!$value instanceof CronJobName) {
            throw new \InvalidArgumentException('Invalid CronJobName value.');
        }

        return $value->value;
    }
}
