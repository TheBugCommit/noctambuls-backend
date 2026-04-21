<?php

declare(strict_types=1);

namespace App\Auth\Domain\Service;

use App\Auth\Domain\Entity\AuthUser;

interface JwtTokenGeneratorInterface
{
    public function generate(AuthUser $user): string;
}
