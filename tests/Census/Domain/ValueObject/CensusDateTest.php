<?php

declare(strict_types=1);

namespace App\Tests\Census\Domain\ValueObject;

use App\Census\Domain\Exception\InvalidCensusDateException;
use App\Census\Domain\ValueObject\CensusDate;
use PHPUnit\Framework\TestCase;

class CensusDateTest extends TestCase
{
    public function testItCreatesAValidDate(): void
    {
        $date = new CensusDate('2026-05-11');

        $this->assertInstanceOf(\DateTimeImmutable::class, $date->value());
        $this->assertSame('2026-05-11', $date->format('Y-m-d'));
        $this->assertSame('2026-05-11', (string) $date);
    }

    public function testItThrowsExceptionOnInvalidDateFormat(): void
    {
        $this->expectException(InvalidCensusDateException::class);

        new CensusDate('not-a-date');
    }

    public function testItThrowsExceptionOnNonExistentDate(): void
    {
        $this->expectException(InvalidCensusDateException::class);

        new CensusDate('2026-01-32');
    }

    public function testItCanCompareTwoDates(): void
    {
        $date1 = new CensusDate('2026-05-11');
        $date2 = new CensusDate('2026-05-11');
        $date3 = new CensusDate('2026-05-12');

        $this->assertTrue($date1->equals($date2));
        $this->assertFalse($date1->equals($date3));
    }
}
