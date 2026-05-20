<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Analytics;

use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsHandler;
use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsResponse;
use App\UI\Http\Rest\Analytics\UpdateAnalyticsController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class UpdateAnalyticsControllerTest extends TestCase
{
    private const string SECRET = 'secret-cron-token';

    private UpdateAnalyticsHandler&MockObject $handler;
    private UpdateAnalyticsController         $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(UpdateAnalyticsHandler::class);
        $this->controller = new UpdateAnalyticsController($this->handler, self::SECRET);
    }

    public function test_invokes_handler_when_valid_token_provided(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->willReturn(new UpdateAnalyticsResponse('2026-05-01T00:00:00Z', 5));

        $request = Request::create('/api/internal/update/analytics', 'POST');
        $request->headers->set('X-Cron-Token', self::SECRET);

        $response = ($this->controller)($request);

        self::assertInstanceOf(UpdateAnalyticsResponse::class, $response);
        self::assertSame(5, $response->dataPointCount);
    }

    public function test_throws_unauthorized_when_token_is_missing(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(UnauthorizedHttpException::class);

        ($this->controller)(Request::create('/api/internal/update/analytics', 'POST'));
    }

    public function test_throws_unauthorized_when_token_is_wrong(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(UnauthorizedHttpException::class);

        $request = Request::create('/api/internal/update/analytics', 'POST');
        $request->headers->set('X-Cron-Token', 'wrong-token');

        ($this->controller)($request);
    }
}
