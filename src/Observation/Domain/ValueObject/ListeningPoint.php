<?php

declare(strict_types=1);

namespace App\Observation\Domain\ValueObject;

use App\Shared\Domain\ValueObject\IntValueObject;

final class ListeningPoint extends IntValueObject
{

    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($value < 0) {
            throw new \InvalidArgumentException('Listening point must be a positive integer.');
        }
    }
}
