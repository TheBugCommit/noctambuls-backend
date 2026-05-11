<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use PHPUnit\Framework\TestCase;
use App\Census\Application\ListCensuses\ListCensusesHandler;
use App\Census\Application\ListCensuses\ListCensusesRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;

final class ListCensusesHandlerTest extends TestCase
{
    public function test_handle_returns_list_and_total(): void
    {
        $repo = $this->createMock(CensusRepositoryInterface::class);

        $repo->method('findPaginated')->willReturn([]);
        $repo->method('count')->willReturn(0);

        $handler = new ListCensusesHandler($repo);

        $response = $handler->handle(new ListCensusesRequest(page: 1, limit: 10));

        $this->assertIsArray($response->censuses);
        $this->assertSame(0, $response->total);
    }
}
