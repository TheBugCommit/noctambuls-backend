<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\ValueObject;

use App\Auth\Domain\ValueObject\Provider;
use PHPUnit\Framework\TestCase;

final class ProviderTest extends TestCase
{
    public function test_google_factory_creates_google_provider(): void
    {
        $provider = Provider::google();

        self::assertSame('google', $provider->value());
    }

    public function test_internal_factory_creates_internal_provider(): void
    {
        $provider = Provider::internal();

        self::assertSame('internal', $provider->value());
    }

    public function test_constructor_accepts_allowed_values(): void
    {
        self::assertSame('google', new Provider('google')->value());
        self::assertSame('internal', new Provider('internal')->value());
    }

    public function test_unsupported_provider_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported provider');

        new Provider('facebook');
    }

    public function test_to_string_returns_value(): void
    {
        self::assertSame('google', (string) Provider::google());
    }

    public function test_equals_returns_true_for_same_provider(): void
    {
        self::assertTrue(Provider::google()->equals(Provider::google()));
    }

    public function test_equals_returns_false_for_different_providers(): void
    {
        self::assertFalse(Provider::google()->equals(Provider::internal()));
    }
}
