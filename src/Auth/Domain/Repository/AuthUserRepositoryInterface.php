<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;

interface AuthUserRepositoryInterface
{
    public function findById(AuthUserId $id): ?AuthUser;
    public function findByEmail(Email $email): ?AuthUser;
    public function findByEmailAndProvider(Email $email, Provider $provider): ?AuthUser;

    /** @return AuthUser[] */
    public function findPaginatedByRole(Role $role, int $page, int $limit): array;
    public function countByRole(Role $role): int;

    /** @param Role[] $roles @return AuthUser[] */
    public function findByRoles(array $roles): array;

    public function save(AuthUser $user): void;
    public function delete(AuthUser $user): void;
    public function deleteRefreshTokensByEmail(Email $email): void;
}
