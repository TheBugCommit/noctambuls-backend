<?php

declare(strict_types=1);

namespace App\Profile\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class OrnithologistCode extends StringValueObject
{
    private const string PATTERN = '/^#OR-[A-Z]{1,4}-\d{4}$/';

    public function __construct(string $value)
    {
        if (!preg_match(self::PATTERN, $value)) {
            throw new \InvalidArgumentException("Invalid Ornithologist Code format: $value");
        }

        parent::__construct($value);
    }

    public static function extractPrefixFromName(ProfileName $name): string
    {
        $cleanName = preg_replace('/[^a-zA-ZÀ-ÿ\s]/', ' ', $name->value());

        $words = preg_split('/\s+/', trim($cleanName), -1, PREG_SPLIT_NO_EMPTY);

        $initials = '';
        foreach ($words as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            if (mb_strlen($initials) >= 4) {
                break;
            }
        }

        if ($initials === '') {
            $initials = 'X';
        }

        return "#OR-$initials-";
    }

    public static function generate(ProfileName $name, int $uniqueNumber): self
    {
        $prefix = self::extractPrefixFromName($name);
        $formattedNumber = str_pad((string) $uniqueNumber, 4, '0', STR_PAD_LEFT);
        return new self($prefix . $formattedNumber);
    }
}
