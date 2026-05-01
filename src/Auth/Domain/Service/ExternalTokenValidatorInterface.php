<?php

declare(strict_types=1);

namespace App\Auth\Domain\Service;

use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;

interface ExternalTokenValidatorInterface
{
    public function validate(string $token, Provider $provider): Email;
}
