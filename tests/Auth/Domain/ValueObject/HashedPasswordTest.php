<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\HashedPassword;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    public function test_valid_bcrypt_hash_is_accepted(): void
    {
        $hash = password_hash('my-secret', PASSWORD_BCRYPT);
        $vo   = new HashedPassword($hash);

        self::assertSame($hash, $vo->value());
    }

    public function test_valid_argon2i_hash_is_accepted(): void
    {
        if (!defined('PASSWORD_ARGON2I')) {
            self::markTestSkipped('Argon2i not supported.');
        }

        $hash = password_hash('my-secret', PASSWORD_ARGON2I);
        $vo   = new HashedPassword($hash);

        self::assertSame($hash, $vo->value());
    }

    public function test_plain_string_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not a valid password hash');

        new HashedPassword('plain-text-password');
    }

    public function test_empty_string_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new HashedPassword('');
    }

    public function test_to_string_returns_hash(): void
    {
        $hash = password_hash('test', PASSWORD_BCRYPT);
        $vo   = new HashedPassword($hash);

        self::assertSame($hash, (string) $vo);
    }

    public function test_equals_returns_true_for_same_hash(): void
    {
        $hash = password_hash('test', PASSWORD_BCRYPT);
        $a    = new HashedPassword($hash);
        $b    = new HashedPassword($hash);

        self::assertTrue($a->equals($b));
    }
}
