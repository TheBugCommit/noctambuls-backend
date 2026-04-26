<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class BirdDescription extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }
        parent::__construct($value);
    }
}
