<?php

declare(strict_types=1);

namespace App\Tests\Profile\Domain\ValueObject;

use App\Profile\Domain\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function test_accepts_phone_with_plus_prefix(): void
    {
        $phone = new PhoneNumber('+34611000000');

        self::assertInstanceOf(PhoneNumber::class, $phone);
    }

    public function test_accepts_nine_digit_phone(): void
    {
        $phone = new PhoneNumber('611000000');

        self::assertSame('611000000', $phone->value());
    }

    public function test_accepts_fifteen_digit_phone(): void
    {
        $phone = new PhoneNumber('123456789012345');

        self::assertInstanceOf(PhoneNumber::class, $phone);
    }

    public function test_value_returns_given_string(): void
    {
        $phone = new PhoneNumber('+34611000000');

        self::assertSame('+34611000000', $phone->value());
    }

    public function test_throws_on_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber('');
    }

    public function test_throws_on_eight_digit_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber('12345678');
    }

    public function test_throws_on_sixteen_digit_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber('1234567890123456');
    }

    public function test_throws_on_letters_in_number(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber('61100000A');
    }

    public function test_throws_on_special_characters_other_than_leading_plus(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumber('611-000-000');
    }
}
