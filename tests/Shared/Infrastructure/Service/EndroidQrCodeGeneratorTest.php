<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Service;

use App\Shared\Infrastructure\Service\EndroidQrCodeGenerator;
use PHPUnit\Framework\TestCase;

final class EndroidQrCodeGeneratorTest extends TestCase
{
    public function test_generate_base64_returns_png_data_uri(): void
    {
        $generator = new EndroidQrCodeGenerator();

        $result = $generator->generateBase64('test data');

        self::assertStringStartsWith('data:image/png;base64,', $result);
    }
}
