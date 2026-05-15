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
        private \DateTimeImmutable          $updatedAt,
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
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return new self($id, $email, $provider, $now, $role, $hashedPassword, false, null, $now);
    }

    public static function createBackofficeUser(
        AuthUserId $id,
        Email $email,
        Role $role,
        HashedPassword $hashedPassword
    ): self {
        if ($role === Role::USER) {
            throw new \DomainException("Backoffice can't have role USER.");
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return new self($id, $email, Provider::internal(), $now, $role, $hashedPassword, false, null, $now);
    }

    public function updateHashedPassword(HashedPassword $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function changeRole(Role $role): void
    {
        $this->role = $role;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function block(): void
    {
        $this->isBlocked = true;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function unblock(): void
    {
        $this->isBlocked = false;
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function anonymize(Email $anonymizedEmail): void
    {
        $this->email = $anonymizedEmail;
        $this->hashedPassword = null;
        $this->isBlocked = true;
        $this->anonymizedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
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

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
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
