<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final class InformationUrl extends StringValueObject
{
    public function __construct(string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException(sprintf('Invalid URL: "%s"', $value));
        }
        parent::__construct($value);
    }
}
