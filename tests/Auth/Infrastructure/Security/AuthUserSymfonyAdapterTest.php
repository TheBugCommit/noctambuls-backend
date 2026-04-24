<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use PHPUnit\Framework\TestCase;

final class AuthUserSymfonyAdapterTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private function makeUser(Role $role = Role::USER, ?string $bcryptHash = null): AuthUser
    {
        $password = $bcryptHash !== null ? new HashedPassword($bcryptHash) : null;

        return AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
            $role,
            $password,
        );
    }

    public function test_get_user_identifier_returns_email(): void
    {
        $adapter = new AuthUserSymfonyAdapter($this->makeUser());

        self::assertSame('user@example.com', $adapter->getUserIdentifier());
    }

    public function test_get_roles_returns_user_role(): void
    {
        $adapter = new AuthUserSymfonyAdapter($this->makeUser(Role::USER));

        self::assertSame(['ROLE_USER'], $adapter->getRoles());
    }

    public function test_get_roles_returns_reviewer_role(): void
    {
        $adapter = new AuthUserSymfonyAdapter($this->makeUser(Role::REVIEWER));

        self::assertSame(['ROLE_REVIEWER'], $adapter->getRoles());
    }

    public function test_get_roles_returns_admin_role(): void
    {
        $adapter = new AuthUserSymfonyAdapter($this->makeUser(Role::ADMIN));

        self::assertSame(['ROLE_ADMIN'], $adapter->getRoles());
    }

    public function test_get_password_returns_hash_when_set(): void
    {
        $hash    = password_hash('secret', PASSWORD_BCRYPT);
        $adapter = new AuthUserSymfonyAdapter($this->makeUser(Role::USER, $hash));

        self::assertSame($hash, $adapter->getPassword());
    }

    public function test_get_password_returns_null_when_not_set(): void
    {
        $adapter = new AuthUserSymfonyAdapter($this->makeUser());

        self::assertNull($adapter->getPassword());
    }

    public function test_get_user_identifier_after_role_change(): void
    {
        $user    = $this->makeUser(Role::USER);
        $adapter = new AuthUserSymfonyAdapter($user);
        $user->changeRole(Role::ADMIN);

        self::assertSame(['ROLE_ADMIN'], $adapter->getRoles());
    }
}
