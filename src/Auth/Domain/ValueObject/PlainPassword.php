<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class PlainPassword extends StringValueObject
{
    public function __construct(string $value)
    {
        $this->validate($value);
        parent::__construct($value);
    }

    private function validate(string $value): void
    {
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        if (!preg_match('/[A-Z]/', $value)) {
            throw new \InvalidArgumentException('Password must contain at least one uppercase letter.');
        }

        if (!preg_match('/[a-z]/', $value)) {
            throw new \InvalidArgumentException('Password must contain at least one lowercase letter.');
        }

        if (!preg_match('/[0-9]/', $value)) {
            throw new \InvalidArgumentException('Password must contain at least one number.');
        }

        if (!preg_match('/[\W_]/', $value)) {
            throw new \InvalidArgumentException('Password must contain at least one special character.');
        }
    }
}
