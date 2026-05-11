<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Backoffice\Census;

use App\Census\Application\ListCensuses\ListCensusesHandler;
use App\Census\Application\ListCensuses\ListCensusesRequest;
use App\Census\Application\ListCensuses\ListCensusesResponse;
use App\UI\Http\Backoffice\Census\CensusListController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

class CensusListControllerTest extends TestCase
{
    private ListCensusesHandler&MockObject $handler;
    private CensusListController $controller;
    private Environment&MockObject $twig;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(ListCensusesHandler::class);
        $this->controller = new CensusListController($this->handler);

        $this->twig = $this->createMock(Environment::class);
        $this->requestStack = new RequestStack();

        $container = new Container();
        $container->set('twig', $this->twig);
        $container->set('request_stack', $this->requestStack);

        $this->controller->setContainer($container);
    }

    private function createRequest(): Request
    {
        $request = new Request();
        $request->setMethod('GET');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);

        return $request;
    }

    public function testItRendersTheListOfCensusesSuccessfully(): void
    {
        $request = $this->createRequest();

        $mockResponse = new ListCensusesResponse([], 0);

        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ListCensusesRequest::class))
            ->willReturn($mockResponse);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('backoffice/census/list.html.twig', ['censuses' => [], 'total' => 0])
            ->willReturn('<html>Llista de Censos</html>');

        $response = ($this->controller)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('<html>Llista de Censos</html>', $response->getContent());
    }
}
