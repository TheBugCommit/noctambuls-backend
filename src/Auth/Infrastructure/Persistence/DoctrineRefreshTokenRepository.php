<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\RefreshToken;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Auth\Domain\ValueObject\TokenString;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(RefreshToken $refreshToken): void
    {
        $this->em->persist($refreshToken);
    }

    public function findByToken(TokenString $token): ?RefreshToken
    {
        return $this->em->getRepository(RefreshToken::class)->findOneBy([
            'token' => $token
        ]);
    }

    public function delete(RefreshToken $refreshToken): void
    {
        $this->em->remove($refreshToken);
    }
}
