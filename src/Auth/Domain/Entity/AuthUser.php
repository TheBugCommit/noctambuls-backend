<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;

readonly class AuthUser
{
    public function __construct(
        private AuthUserId         $id,
        private Email              $email,
        private Provider           $provider,
        private \DateTimeImmutable $createdAt
    ) {
    }

    public static function create(AuthUserId $id, Email $email, Provider $provider): self
    {
        return new self($id, $email, $provider, new \DateTimeImmutable());
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
}
