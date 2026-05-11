<?php

declare(strict_types=1);

namespace App\Tests\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusTimeException;
use App\Census\Domain\ValueObject\CensusTime;
use PHPUnit\Framework\TestCase;

class CensusTimeTest extends TestCase
{
    public function testItCreatesAValidTime(): void
    {
        $time = new CensusTime('15:30');

        $this->assertSame('15:30', $time->format('H:i'));
    }

    public function testItThrowsExceptionOnInvalidTimeFormat(): void
    {
        $this->expectException(InvalidCensusTimeException::class);

        new CensusTime('25:99');
    }
}
