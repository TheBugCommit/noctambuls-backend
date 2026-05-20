<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Census;

use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesHandler;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesRequest;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesResponse;
use App\UI\Http\Rest\Census\GetPublishedCensusesController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetPublishedCensusesControllerTest extends TestCase
{
    private GetPublishedCensusesHandler&MockObject $handler;
    private GetPublishedCensusesController         $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(GetPublishedCensusesHandler::class);
        $this->controller = new GetPublishedCensusesController($this->handler);
    }

    public function test_invokes_handler_without_since_when_not_provided(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->with(self::callback(fn(GetPublishedCensusesRequest $req) => $req->since === null))
            ->willReturn(new GetPublishedCensusesResponse([], '2026-01-01T00:00:00Z'));

        $response = ($this->controller)(Request::create('/api/censuses', 'GET'));

        self::assertSame([], $response->censuses);
    }

    public function test_invokes_handler_with_parsed_since_when_valid_date_provided(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->with(self::callback(fn(GetPublishedCensusesRequest $req) => $req->since instanceof \DateTimeImmutable))
            ->willReturn(new GetPublishedCensusesResponse([], '2026-05-01T00:00:00Z'));

        ($this->controller)(Request::create('/api/censuses', 'GET', ['since' => '2026-05-01T00:00:00Z']));
    }

    public function test_throws_bad_request_when_since_is_invalid(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(BadRequestHttpException::class);

        ($this->controller)(Request::create('/api/censuses', 'GET', ['since' => 'not-a-date']));
    }
}
