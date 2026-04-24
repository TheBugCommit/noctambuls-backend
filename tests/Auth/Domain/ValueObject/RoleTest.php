<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function test_user_role_has_correct_value(): void
    {
        self::assertSame('ROLE_USER', Role::USER->value);
    }

    public function test_reviewer_role_has_correct_value(): void
    {
        self::assertSame('ROLE_REVIEWER', Role::REVIEWER->value);
    }

    public function test_admin_role_has_correct_value(): void
    {
        self::assertSame('ROLE_ADMIN', Role::ADMIN->value);
    }

    public function test_default_returns_user_role(): void
    {
        self::assertSame(Role::USER, Role::default());
    }

    public function test_from_valid_string(): void
    {
        self::assertSame(Role::USER, Role::from('ROLE_USER'));
        self::assertSame(Role::REVIEWER, Role::from('ROLE_REVIEWER'));
        self::assertSame(Role::ADMIN, Role::from('ROLE_ADMIN'));
    }

    public function test_from_invalid_string_throws(): void
    {
        $this->expectException(\ValueError::class);

        Role::from('ROLE_SUPERADMIN');
    }

    public function test_tryFrom_invalid_returns_null(): void
    {
        self::assertNull(Role::tryFrom('ROLE_UNKNOWN'));
    }

    public function test_tryFrom_valid_returns_case(): void
    {
        self::assertSame(Role::ADMIN, Role::tryFrom('ROLE_ADMIN'));
    }

    public function test_all_cases_present(): void
    {
        $cases = Role::cases();

        self::assertCount(3, $cases);
    }
}
