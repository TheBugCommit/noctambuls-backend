<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class Provider extends StringValueObject
{
    private const array ALLOWED = ['google', 'internal'];

    public function __construct(string $value)
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported provider: "%s"', $value));
        }
        parent::__construct($value);
    }

    public static function google(): self
    {
        return new self('google');
    }

    public static function internal(): self
    {
        return new self('internal');
    }
}
