<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

final class ImagePath extends StringValueObject
{
    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Image path cannot be empty.');
        }
        parent::__construct($value);
    }
}
