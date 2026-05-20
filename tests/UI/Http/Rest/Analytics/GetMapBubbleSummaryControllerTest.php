<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Analytics;

use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryHandler;
use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryRequest;
use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryResponse;
use App\UI\Http\Rest\Analytics\GetMapBubbleSummaryController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetMapBubbleSummaryControllerTest extends TestCase
{
    private GetMapBubbleSummaryHandler&MockObject $handler;
    private GetMapBubbleSummaryController         $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(GetMapBubbleSummaryHandler::class);
        $this->controller = new GetMapBubbleSummaryController($this->handler);
    }

    public function test_invokes_handler_without_since_when_not_provided(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->with(self::callback(fn(GetMapBubbleSummaryRequest $req) => $req->since === null))
            ->willReturn(new GetMapBubbleSummaryResponse(null, []));

        $response = ($this->controller)(Request::create('/api/analytics/map-bubbles', 'GET'));

        self::assertNull($response->serverTime);
        self::assertSame([], $response->items);
    }

    public function test_invokes_handler_with_since_when_valid_iso8601_provided(): void
    {
        $since = '2026-05-01T00:00:00Z';

        $this->handler->expects(self::once())
            ->method('handle')
            ->with(self::callback(fn(GetMapBubbleSummaryRequest $req) => $req->since === $since))
            ->willReturn(new GetMapBubbleSummaryResponse('2026-05-01T00:00:00Z', []));

        ($this->controller)(Request::create('/api/analytics/map-bubbles', 'GET', ['since' => $since]));
    }

    public function test_throws_bad_request_when_since_format_is_invalid(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(BadRequestHttpException::class);

        ($this->controller)(Request::create('/api/analytics/map-bubbles', 'GET', ['since' => 'not-a-date']));
    }
}
