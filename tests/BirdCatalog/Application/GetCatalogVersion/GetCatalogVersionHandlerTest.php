<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\GetCatalogVersion;

use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionHandler;
use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionResponse;
use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCatalogVersionHandlerTest extends TestCase
{
    private BirdCatalogVersionRepositoryInterface&MockObject $versionRepository;
    private GetCatalogVersionHandler                         $handler;

    protected function setUp(): void
    {
        $this->versionRepository = $this->createMock(BirdCatalogVersionRepositoryInterface::class);
        $this->handler           = new GetCatalogVersionHandler($this->versionRepository);
    }

    public function test_handle_returns_version_response(): void
    {
        $this->versionRepository->method('get')->willReturn(BirdCatalogVersion::initialize());

        $response = $this->handler->handle();

        self::assertInstanceOf(GetCatalogVersionResponse::class, $response);
        self::assertSame(0, $response->version);
    }
}
