<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\ValueObject;

use App\Observation\Domain\ValueObject\Bearing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BearingTest extends TestCase
{
    #[DataProvider('validValuesProvider')]
    public function test_it_creates_bearing_with_valid_values(float $value): void
    {
        $bearing = new Bearing($value);
        $this->assertSame($value, $bearing->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function test_it_throws_exception_for_invalid_bearing(float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Bearing($value);
    }

    public static function validValuesProvider(): array
    {
        return [
            'minimum valid (North)' => [0.0],
            'East' => [90.0],
            'South' => [180.0],
            'West' => [270.0],
            'almost full circle' => [359.9],
        ];
    }

    public static function invalidValuesProvider(): array
    {
        return [
            'above 360' => [361.0],
            'below -360' => [-361.0],
        ];
    }
}
