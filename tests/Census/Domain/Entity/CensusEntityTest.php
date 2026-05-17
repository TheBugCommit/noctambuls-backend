<?php

declare(strict_types=1);

namespace App\Tests\Census\Domain\Entity;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\Exception\CensusNotEditableException;
use App\Census\Domain\ValueObject\CensusDate;
use App\Census\Domain\ValueObject\CensusDescription;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusTime;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\TestCase;

class CensusEntityTest extends TestCase
{
    private function createValidCensus(): Census
    {
        return Census::create(
            new CensusId('8be4df61-93ca-11d2-aa0d-00e098032b8c'),
            new CensusTitle('Cens del Bosc'),
            new CensusDate('2026-05-11'),
            new CensusTime('10:00'),
            new CensusLocationName('Igualada'),
            new Latitude(41.5819),
            new Longitude(1.6177),
            new CensusDescription('Descripció detallada del cens')
        );
    }

    public function testItCreatesACensusCorrectly(): void
    {
        $census = $this->createValidCensus();

        $this->assertSame('8be4df61-93ca-11d2-aa0d-00e098032b8c', $census->id()->value());
        $this->assertSame('Cens del Bosc', $census->title()->value());
        $this->assertSame(41.5819, $census->latitude()->value());

        $this->assertTrue($census->status()->isDraft());
    }

    public function testItUpdatesACensusSuccessfully(): void
    {
        $census = $this->createValidCensus();

        $census->update(
            new CensusTitle('Nou Títol'),
            new CensusDate('2026-06-01'),
            new CensusTime('11:00'),
            new CensusLocationName('Nou Lloc'),
            new Latitude(42.0),
            new Longitude(2.0),
            null
        );

        $this->assertSame('Nou Títol', $census->title()->value());
        $this->assertSame('2026-06-01', $census->date()->format('Y-m-d'));
        $this->assertSame(42.0, $census->latitude()->value());
        $this->assertNull($census->description());
    }

    public function testItAssertsEditableWhenAllowed(): void
    {
        $census = $this->createValidCensus();

        $this->expectNotToPerformAssertions();
        $census->assertEditable();
    }

    public function testItCanEditPublishedCensusAfterGracePeriod(): void
    {
        $census = $this->createValidCensus();
        $census->publish();

        $reflection = new \ReflectionClass($census);
        $prop = $reflection->getProperty('publishedAt');
        $prop->setAccessible(true);
        $prop->setValue($census, new \DateTimeImmutable('-20 minutes'));

        $this->expectNotToPerformAssertions();
        $census->assertEditable();
    }

    public function testSoftDeletedCensusIsNotEditable(): void
    {
        $census = $this->createValidCensus();
        $census->publish();
        $census->softDelete();

        $this->expectException(CensusNotEditableException::class);
        $census->assertEditable();
    }

    public function testSoftDeleteSetsFields(): void
    {
        $census = $this->createValidCensus();
        $census->publish();

        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $census->softDelete();

        $this->assertTrue($census->isDeleted());
        $this->assertNotNull($census->deletedAt());
        $this->assertGreaterThanOrEqual($before, $census->deletedAt());
    }

    public function testIsWithinGracePeriodReturnsTrueWhenRecentlyPublished(): void
    {
        $census = $this->createValidCensus();
        $census->publish();

        $this->assertTrue($census->isWithinGracePeriod());
    }

    public function testIsWithinGracePeriodReturnsFalseAfterTenMinutes(): void
    {
        $census = $this->createValidCensus();
        $census->publish();

        $reflection = new \ReflectionClass($census);
        $prop = $reflection->getProperty('publishedAt');
        $prop->setAccessible(true);
        $prop->setValue($census, new \DateTimeImmutable('-20 minutes'));

        $this->assertFalse($census->isWithinGracePeriod());
    }
}
