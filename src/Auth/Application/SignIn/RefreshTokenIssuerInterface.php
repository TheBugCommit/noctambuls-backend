<?php

namespace App\Auth\Application\SignIn;

use App\Auth\Domain\Entity\AuthUser;

interface RefreshTokenIssuerInterface
{
    public function issueTokenForUser(AuthUser $user): string;
}
