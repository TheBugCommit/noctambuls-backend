<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Auth\Infrastructure\Security\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAuthUserRepository implements AuthUserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findByEmailAndProvider(Email $email, Provider $provider): ?AuthUser
    {
        return $this->em->getRepository(AuthUser::class)->findOneBy([
            'email'    => $email,
            'provider' => $provider,
        ]);
    }

    public function findByEmail(Email $email): ?AuthUser
    {
        return $this->em->getRepository(AuthUser::class)->findOneBy([
            'email' => $email,
        ]);
    }

    public function findById(AuthUserId $id): ?AuthUser
    {
        return $this->em->getRepository(AuthUser::class)->find($id->value());
    }

    public function findPaginatedByRole(Role $role, int $page, int $limit): array
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(AuthUser::class, 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByRole(Role $role): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(AuthUser::class, 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByRoles(array $roles): array
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(AuthUser::class, 'u')
            ->where('u.role IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(AuthUser $user): void
    {
        $this->em->persist($user);
    }

    public function delete(AuthUser $user): void
    {
        $this->em->remove($user);
    }

    public function deleteRefreshTokensByEmail(Email $email): void
    {
        $this->em->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.username = :email')
            ->setParameter('email', $email->value())
            ->getQuery()
            ->execute();
    }
}
