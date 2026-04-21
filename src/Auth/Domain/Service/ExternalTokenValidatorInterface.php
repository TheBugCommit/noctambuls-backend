<?php

declare(strict_types=1);

namespace App\Auth\Domain\Service;

use App\Auth\Domain\ValueObject\Email;

interface ExternalTokenValidatorInterface
{
    public function validate(string $token): Email;
}
