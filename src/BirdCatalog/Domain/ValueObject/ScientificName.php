<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class ScientificName extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Scientific name cannot be empty.');
        }
        parent::__construct($value);
    }
}
