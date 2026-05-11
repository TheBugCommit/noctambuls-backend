<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\ValueObject;

use App\Observation\Domain\ValueObject\Accuracy;
use App\Observation\Domain\ValueObject\AltitudeAccuracy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AccuracyTest extends TestCase
{
    #[DataProvider('validValuesProvider')]
    public function test_it_creates_accuracy_with_valid_values(float $value): void
    {
        $accuracy = new Accuracy($value);
        $this->assertSame($value, $accuracy->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function test_it_throws_exception_for_invalid_accuracy(float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Accuracy($value);
    }

    #[DataProvider('validValuesProvider')]
    public function test_it_creates_altitude_accuracy_with_valid_values(float $value): void
    {
        $accuracy = new AltitudeAccuracy($value);
        $this->assertSame($value, $accuracy->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function test_it_throws_exception_for_invalid_altitude_accuracy(float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AltitudeAccuracy($value);
    }

    public static function validValuesProvider(): array
    {
        return [
            'minimum valid' => [0.0],
            'middle value' => [500.5],
            'maximum valid' => [10000.0],
        ];
    }

    public static function invalidValuesProvider(): array
    {
        return [
            'below minimum' => [-0.1],
            'negative value' => [-100.0],
            'above maximum' => [10000.1],
        ];
    }
}
