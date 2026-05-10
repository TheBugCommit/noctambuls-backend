<?php

declare(strict_types=1);

namespace App\Census\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class CensusTitle extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Census title cannot be empty.');
        }

        parent::__construct($value);
    }
}
