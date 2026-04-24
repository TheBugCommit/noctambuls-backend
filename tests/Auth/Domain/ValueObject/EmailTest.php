<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function test_valid_email_is_accepted(): void
    {
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', $email->value());
    }

    public function test_invalid_email_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');

        new Email('not-an-email');
    }

    public function test_empty_string_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('');
    }

    public function test_to_string_returns_value(): void
    {
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', (string) $email);
    }

    public function test_equals_returns_true_for_same_email(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('user@example.com');

        self::assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_emails(): void
    {
        $a = new Email('user@example.com');
        $b = new Email('other@example.com');

        self::assertFalse($a->equals($b));
    }

    #[DataProvider('validEmails')]
    public function test_various_valid_email_formats_are_accepted(string $email): void
    {
        self::assertSame($email, new Email($email)->value());
    }

    public static function validEmails(): array
    {
        return [
            ['simple@example.com'],
            ['user.name+tag@sub.domain.org'],
            ['user123@company.co.uk'],
        ];
    }
}
