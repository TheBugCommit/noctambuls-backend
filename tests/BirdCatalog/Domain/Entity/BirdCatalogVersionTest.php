<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Domain\Entity;

use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;
use PHPUnit\Framework\TestCase;

final class BirdCatalogVersionTest extends TestCase
{
    public function test_initialize_creates_version_zero(): void
    {
        $version = BirdCatalogVersion::initialize();

        self::assertSame(0, $version->version());
    }

    public function test_initialize_has_id_one(): void
    {
        $version = BirdCatalogVersion::initialize();

        self::assertSame(1, $version->id());
    }

    public function test_increment_increases_version_by_one(): void
    {
        $version = BirdCatalogVersion::initialize();

        $version->increment();

        self::assertSame(1, $version->version());
    }

    public function test_increment_updates_last_updated_at(): void
    {
        $version = BirdCatalogVersion::initialize();
        $before  = $version->lastUpdatedAt();

        usleep(1000);
        $version->increment();

        self::assertGreaterThanOrEqual($before, $version->lastUpdatedAt());
    }
}
