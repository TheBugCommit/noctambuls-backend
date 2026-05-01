<?php

declare(strict_types=1);

namespace App\Profile\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class ProfileName extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Profile name cannot be empty.');
        }

        parent::__construct($value);
    }
}
