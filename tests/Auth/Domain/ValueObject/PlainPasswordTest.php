<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\PlainPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlainPasswordTest extends TestCase
{
    public function test_valid_password_is_accepted(): void
    {
        $password = new PlainPassword('ValidPass1!');

        self::assertSame('ValidPass1!', $password->value());
    }

    public function test_minimum_valid_password(): void
    {
        $password = new PlainPassword('Aa1!aaaa');

        self::assertSame('Aa1!aaaa', $password->value());
    }

    #[DataProvider('invalidPasswords')]
    public function test_invalid_password_throws(string $password, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new PlainPassword($password);
    }

    public static function invalidPasswords(): array
    {
        return [
            'too short'          => ['Aa1!', 'Password must be at least 8 characters.'],
            'no uppercase'       => ['validpass1!', 'Password must contain at least one uppercase letter.'],
            'no lowercase'       => ['VALIDPASS1!', 'Password must contain at least one lowercase letter.'],
            'no number'          => ['ValidPass!!', 'Password must contain at least one number.'],
            'no special char'    => ['ValidPass1a', 'Password must contain at least one special character.'],
        ];
    }
}
