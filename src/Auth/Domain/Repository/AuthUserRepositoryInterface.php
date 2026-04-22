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

    public function save(AuthUser $user): void;
}
