<?php
declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\RefreshTokenId;
use App\Auth\Domain\ValueObject\TokenString;

readonly class RefreshToken
{
    public function __construct(
        private RefreshTokenId $id,
        private TokenString $token,
        private AuthUserId $userId,
        private \DateTimeImmutable $expiresAt
    ) {
    }

    public static function create(RefreshTokenId $id, TokenString $token, AuthUserId $userId): self {
        return new self($id, $token, $userId, new \DateTimeImmutable()->modify('+ 3 months'));
    }

    public function id(): RefreshTokenId
    {
        return $this->id;
    }

    public function token(): TokenString
    {
        return $this->token;
    }

    public function userId(): AuthUserId
    {
        return $this->userId;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
}
