<?php

declare(strict_types=1);

namespace App\Profile\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class PhoneNumber extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Phone number cannot be empty.');
        }

        if (!preg_match('/^\+?[0-9]{9,15}$/', $value)) {
            throw new \InvalidArgumentException("Invalid phone number format: $value.");
        }

        parent::__construct($value);
    }
}
