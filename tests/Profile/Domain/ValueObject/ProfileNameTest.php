<?php

declare(strict_types=1);

namespace App\Tests\Profile\Domain\ValueObject;

use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\TestCase;

final class ProfileNameTest extends TestCase
{
    public function test_accepts_valid_name(): void
    {
        $name = new ProfileName('Joan');

        self::assertInstanceOf(ProfileName::class, $name);
    }

    public function test_value_returns_given_string(): void
    {
        $name = new ProfileName('Joan Doe');

        self::assertSame('Joan Doe', $name->value());
    }

    public function test_throws_on_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ProfileName('');
    }

    public function test_throws_on_whitespace_only_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ProfileName('   ');
    }
}
