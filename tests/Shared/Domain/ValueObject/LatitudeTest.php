<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;
use App\Shared\Domain\ValueObject\Latitude;
use PHPUnit\Framework\TestCase;

class LatitudeTest extends TestCase
{
    public function testItCreatesAValidLatitude(): void
    {
        $latitude = new Latitude(41.5819);
        $this->assertSame(41.5819, $latitude->value());

        $latitudeStr = new Latitude('41.5819');
        $this->assertSame(41.5819, $latitudeStr->value());

        $this->assertSame(90.0, new Latitude(90.0)->value());
        $this->assertSame(-90.0, new Latitude(-90.0)->value());
    }

    public function testItThrowsExceptionWhenEmptyOrNull(): void
    {
        $this->expectException(EmptyCoordinateException::class);
        $this->expectExceptionMessage('Latitude coordinate cannot be empty');

        new Latitude(null);
    }

    public function testItThrowsExceptionWhenEmptyString(): void
    {
        $this->expectException(EmptyCoordinateException::class);
        new Latitude('');
    }

    public function testItThrowsExceptionWhenNotNumeric(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        $this->expectExceptionMessage('Coordinate must be numeric');

        new Latitude('abc');
    }

    public function testItThrowsExceptionWhenOutOfBoundsTooHigh(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        $this->expectExceptionMessage('Invalid latitude');

        new Latitude(90.1);
    }

    public function testItThrowsExceptionWhenOutOfBoundsTooLow(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        new Latitude(-90.1);
    }
}
