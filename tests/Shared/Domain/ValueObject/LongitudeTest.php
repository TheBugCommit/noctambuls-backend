<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\TestCase;

class LongitudeTest extends TestCase
{
    public function testItCreatesAValidLongitude(): void
    {
        $longitude = new Longitude(1.6177);
        $this->assertSame(1.6177, $longitude->value());

        // Límits
        $this->assertSame(180.0, new Longitude(180.0)->value());
        $this->assertSame(-180.0, new Longitude(-180.0)->value());
    }

    public function testItThrowsExceptionWhenOutOfBoundsTooHigh(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        $this->expectExceptionMessage('Invalid longitude');

        new Longitude(180.1);
    }

    public function testItThrowsExceptionWhenOutOfBoundsTooLow(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        new Longitude(-180.1);
    }

}
