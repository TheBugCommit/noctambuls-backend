<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\BirdCatalog;

use App\BirdCatalog\Application\GetCatalog\GetCatalogHandler;
use App\BirdCatalog\Application\GetCatalog\GetCatalogResponse;
use App\UI\Http\Rest\BirdCatalog\GetCatalogController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCatalogControllerTest extends TestCase
{
    private GetCatalogHandler&MockObject $handler;
    private GetCatalogController         $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(GetCatalogHandler::class);
        $this->controller = new GetCatalogController($this->handler);
    }

    public function test_invokes_handler_and_returns_response(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->willReturn(new GetCatalogResponse([]));

        $response = ($this->controller)();

        self::assertSame([], $response->birds);
    }
}
