<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\Entity;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

final class AuthUserTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    public function test_create_returns_user_with_correct_data(): void
    {
        $id       = new AuthUserId(self::UUID);
        $email    = new Email('user@example.com');
        $provider = Provider::google();

        $user = AuthUser::create($id, $email, $provider);

        self::assertSame(self::UUID, $user->id()->value());
        self::assertSame('user@example.com', $user->email()->value());
        self::assertSame('google', $user->provider()->value());
        self::assertSame(Role::USER, $user->role());
        self::assertNull($user->hashedPassword());
        self::assertInstanceOf(\DateTimeImmutable::class, $user->createdAt());
    }

    public function test_create_accepts_explicit_role(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('admin@example.com'),
            Provider::internal(),
            Role::ADMIN,
        );

        self::assertSame(Role::ADMIN, $user->role());
    }

    public function test_create_accepts_hashed_password(): void
    {
        $hash = password_hash('secret', PASSWORD_BCRYPT);
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('admin@example.com'),
            Provider::internal(),
            Role::ADMIN,
            new HashedPassword($hash),
        );

        self::assertSame($hash, $user->hashedPassword()?->value());
    }

    public function test_changeRole_updates_role(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
        );

        self::assertSame(Role::USER, $user->role());

        $user->changeRole(Role::REVIEWER);

        self::assertSame(Role::REVIEWER, $user->role());
    }

    public function test_changeRole_to_admin(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
            Role::REVIEWER,
        );

        $user->changeRole(Role::ADMIN);

        self::assertSame(Role::ADMIN, $user->role());
    }

    public function test_updateHashedPassword_sets_password(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('admin@example.com'),
            Provider::internal(),
        );

        self::assertNull($user->hashedPassword());

        $hash = password_hash('new-password', PASSWORD_BCRYPT);
        $user->updateHashedPassword(new HashedPassword($hash));

        self::assertSame($hash, $user->hashedPassword()?->value());
    }

    public function test_updateHashedPassword_replaces_existing_password(): void
    {
        $hash1 = password_hash('old-password', PASSWORD_BCRYPT);
        $hash2 = password_hash('new-password', PASSWORD_BCRYPT);

        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('admin@example.com'),
            Provider::internal(),
            Role::ADMIN,
            new HashedPassword($hash1),
        );

        $user->updateHashedPassword(new HashedPassword($hash2));

        self::assertSame($hash2, $user->hashedPassword()?->value());
    }

    public function test_createdAt_is_set_on_creation(): void
    {
        $before = new \DateTimeImmutable();
        $user   = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
        );
        $after  = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $user->createdAt());
        self::assertLessThanOrEqual($after, $user->createdAt());
    }
}
