<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;
use App\Auth\Domain\Service\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private PasswordHasherFactoryInterface $hasherFactory)    {
    }

    public function hash(string $plainPassword): string
    {
        $hasher = $this->hasherFactory->getPasswordHasher(AuthUserSymfonyAdapter::class);
        return $hasher->hash($plainPassword);
    }
}
