<?php

namespace App\Auth\Domain\Service;

use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;

interface ProviderTokenValidatorInterface
{
    public function supports(Provider $provider): bool;

    public function validate(string $token): Email;
}
