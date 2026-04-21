<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAuthUserRepository implements AuthUserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findByEmailAndProvider(Email $email, Provider $provider): ?AuthUser
    {
        return $this->em->getRepository(AuthUser::class)->findOneBy([
            'email' => $email,
            'provider' => $provider,
        ]);
    }

    public function findById(AuthUserId $id): ?AuthUser
    {
        return $this->em->getRepository(AuthUser::class)->findOneBy([
            'id' => $id->value(),
        ]);
    }

    public function save(AuthUser $user): void
    {
        $this->em->persist($user);
    }
}
