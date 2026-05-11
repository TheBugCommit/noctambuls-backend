<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\ValueObject;

use App\Observation\Domain\ValueObject\ListeningPoint;
use App\Observation\Domain\ValueObject\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IntegerLimitsTest extends TestCase
{
    #[DataProvider('validListeningPointProvider')]
    public function test_it_creates_listening_point_with_valid_values(int $value): void
    {
        $point = new ListeningPoint($value);
        $this->assertSame($value, $point->value());
    }

    public function test_it_throws_exception_for_negative_listening_point(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ListeningPoint(-1);
    }

    public static function validListeningPointProvider(): array
    {
        return [
            'zero' => [0],
            'positive integer' => [5],
            'large integer' => [100],
        ];
    }

    #[DataProvider('validQuantityProvider')]
    public function test_it_creates_quantity_with_valid_values(int $value): void
    {
        $quantity = new Quantity($value);
        $this->assertSame($value, $quantity->value());
    }

    #[DataProvider('invalidQuantityProvider')]
    public function test_it_throws_exception_for_invalid_quantity(int $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Quantity($value);
    }

    public static function validQuantityProvider(): array
    {
        return [
            'minimum valid' => [1],
            'typical quantity' => [5],
            'large quantity' => [1000],
        ];
    }

    public static function invalidQuantityProvider(): array
    {
        return [
            'zero is invalid' => [0],
            'negative quantity' => [-1],
            'large negative quantity' => [-50],
        ];
    }
}
