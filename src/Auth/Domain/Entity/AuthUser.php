<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;

class AuthUser
{
    public function __construct(
        private readonly AuthUserId         $id,
        private Email                       $email,
        private readonly Provider           $provider,
        private readonly \DateTimeImmutable $createdAt,
        private Role                        $role = Role::USER,
        private ?HashedPassword             $hashedPassword = null,
        private bool                        $isBlocked = false,
        private ?\DateTimeImmutable         $anonymizedAt = null,
    )
    {
    }

    public static function create(
        AuthUserId      $id,
        Email           $email,
        Provider        $provider,
        Role            $role = Role::USER,
        ?HashedPassword $hashedPassword = null,
    ): self
    {
        return new self($id, $email, $provider, new \DateTimeImmutable(), $role, $hashedPassword);
    }

    public function updateHashedPassword(HashedPassword $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function changeRole(Role $role): void
    {
        $this->role = $role;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function block(): void
    {
        $this->isBlocked = true;
    }

    public function unblock(): void
    {
        $this->isBlocked = false;
    }

    public function anonymize(Email $anonymizedEmail): void
    {
        $this->email = $anonymizedEmail;
        $this->hashedPassword = null;
        $this->isBlocked = true;
        $this->anonymizedAt = new \DateTimeImmutable();
    }

    public function id(): AuthUserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function provider(): Provider
    {
        return $this->provider;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function hashedPassword(): ?HashedPassword
    {
        return $this->hashedPassword;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymizedAt !== null;
    }

    public function anonymizedAt(): ?\DateTimeImmutable
    {
        return $this->anonymizedAt;
    }
}
