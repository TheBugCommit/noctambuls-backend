<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class HashedPassword extends StringValueObject
{
    public function __construct(string $value)
    {
        $info = password_get_info($value);
        if ($info['algo'] === null) {
            throw new \InvalidArgumentException('The provided string is not a valid password hash.');
        }

        parent::__construct($value);
    }
}
