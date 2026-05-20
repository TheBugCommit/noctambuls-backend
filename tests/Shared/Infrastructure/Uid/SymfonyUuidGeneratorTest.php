<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Uid;

use App\Shared\Infrastructure\Uid\SymfonyUuidGenerator;
use PHPUnit\Framework\TestCase;

final class SymfonyUuidGeneratorTest extends TestCase
{
    public function test_generate_returns_valid_rfc4122_uuid_v4(): void
    {
        $generator = new SymfonyUuidGenerator();

        $uuid = $generator->generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
        );
    }
}
