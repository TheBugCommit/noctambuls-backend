<?php
declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;
use App\Shared\Domain\ValueObject\StringValueObject;

final class TokenString extends StringValueObject
{

    public static function generate(): self {
        return new self(bin2hex(random_bytes(32)));
    }
}
