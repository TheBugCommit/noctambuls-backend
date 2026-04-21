<?php
declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\RefreshToken;
use App\Auth\Domain\ValueObject\TokenString;

interface RefreshTokenRepositoryInterface
{
    public function save(RefreshToken $refreshToken): void;
    public function findByToken(TokenString $token): ?RefreshToken;
    public function delete(RefreshToken $refreshToken): void;
}
