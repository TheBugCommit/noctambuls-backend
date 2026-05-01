<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface JwtAuthenticatedUserInterface extends UserInterface
{
    public function getAuthUserId(): string;
}
