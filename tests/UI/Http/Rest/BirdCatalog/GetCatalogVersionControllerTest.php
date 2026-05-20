<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\BirdCatalog;

use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionHandler;
use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionResponse;
use App\UI\Http\Rest\BirdCatalog\GetCatalogVersionController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCatalogVersionControllerTest extends TestCase
{
    private GetCatalogVersionHandler&MockObject $handler;
    private GetCatalogVersionController         $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(GetCatalogVersionHandler::class);
        $this->controller = new GetCatalogVersionController($this->handler);
    }

    public function test_invokes_handler_and_returns_response(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->willReturn(new GetCatalogVersionResponse(5, '2026-01-01T00:00:00Z'));

        $response = ($this->controller)();

        self::assertSame(5, $response->version);
        self::assertSame('2026-01-01T00:00:00Z', $response->lastUpdatedAt);
    }
}
