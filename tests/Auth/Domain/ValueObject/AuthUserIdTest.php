<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\AuthUserId;
use PHPUnit\Framework\TestCase;

final class AuthUserIdTest extends TestCase
{
    public function test_valid_uuid_is_accepted(): void
    {
        $id = new AuthUserId('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value());
    }

    public function test_invalid_uuid_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AuthUserId('not-a-uuid');
    }

    public function test_empty_string_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AuthUserId('');
    }

    public function test_to_string_returns_value(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id   = new AuthUserId($uuid);

        self::assertSame($uuid, (string) $id);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        self::assertTrue((new AuthUserId($uuid))->equals(new AuthUserId($uuid)));
    }

    public function test_equals_is_case_insensitive(): void
    {
        $lower = new AuthUserId('550e8400-e29b-41d4-a716-446655440000');
        $upper = new AuthUserId('550E8400-E29B-41D4-A716-446655440000');

        self::assertTrue($lower->equals($upper));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $a = new AuthUserId('550e8400-e29b-41d4-a716-446655440000');
        $b = new AuthUserId('660e8400-e29b-41d4-a716-446655440001');

        self::assertFalse($a->equals($b));
    }
}
