<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\AbstractRefreshToken;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens')]
#[ORM\Index(name: 'idx_refresh_tokens_username', columns: ['username'])]
class RefreshToken extends AbstractRefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int|string|null $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $refreshToken = null;

    #[ORM\Column(type: 'string')]
    protected ?string $username = null;

    #[ORM\Column(type: 'datetime')]
    protected ?\DateTimeInterface $valid = null;
}
