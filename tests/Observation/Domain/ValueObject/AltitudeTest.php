<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\ValueObject;

use App\Observation\Domain\ValueObject\Altitude;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AltitudeTest extends TestCase
{
    #[DataProvider('validValuesProvider')]
    public function test_it_creates_altitude_with_valid_values(float $value): void
    {
        $altitude = new Altitude($value);
        $this->assertSame($value, $altitude->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function test_it_throws_exception_for_invalid_altitude(float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Altitude($value);
    }

    public static function validValuesProvider(): array
    {
        return [
            'minimum valid' => [-500.0],
            'sea level' => [0.0],
            'mountain peak' => [3000.5],
            'maximum valid' => [12000.0],
        ];
    }

    public static function invalidValuesProvider(): array
    {
        return [
            'below minimum' => [-500.1],
            'way below minimum' => [-1000.0],
            'above maximum' => [12000.1],
            'way above maximum' => [15000.0],
        ];
    }
}
