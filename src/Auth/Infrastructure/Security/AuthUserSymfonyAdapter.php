<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Entity\AuthUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthUserSymfonyAdapter implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(private AuthUser $authUser)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->authUser->email()->value();
    }

    public function getRoles(): array
    {
        return [$this->authUser->role()->value];
    }

    public function getPassword(): ?string
    {
        return $this->authUser->hashedPassword()?->value();
    }

    public function getAuthUser(): AuthUser
    {
        return $this->authUser;
    }
}
