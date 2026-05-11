<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Backoffice\Census;

use App\Census\Application\GetCensus\GetCensusHandler;
use App\Census\Application\GetCensus\GetCensusResponse;
use App\Census\Application\UpdateCensus\UpdateCensusHandler;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\UI\Http\Backoffice\Census\CensusEditController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class CensusEditControllerTest extends TestCase
{
    private RequestStack $requestStack;

    private GetCensusHandler&MockObject $getHandler;
    private UpdateCensusHandler&MockObject $updateHandler;
    private CensusEditController $controller;
    private RouterInterface&MockObject $router;
    private Environment&MockObject $twig;
    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;

    protected function setUp(): void
    {
        $this->getHandler = $this->createMock(GetCensusHandler::class);
        $this->updateHandler = $this->createMock(UpdateCensusHandler::class);

        $this->controller = new CensusEditController($this->getHandler, $this->updateHandler);

        $this->twig = $this->createMock(Environment::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->requestStack = new RequestStack();
        $container = new Container();
        $container->set('twig', $this->twig);
        $container->set('router', $this->router);
        $container->set('security.csrf.token_manager', $this->csrfTokenManager);
        $container->set('request_stack', $this->requestStack);
        $this->controller->setContainer($container);
    }

    private function createRequest(string $method = 'POST'): Request
    {
        $request = new Request();
        $request->setMethod($method);
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);
        return $request;
    }

    public function testItRedirectsToListIfCensusNotFound(): void
    {
        $request = $this->createRequest('GET');

        $this->getHandler->method('handle')->willThrowException(new CensusNotFoundException());

        $this->router->expects($this->once())
            ->method('generate')
            ->with('backoffice_census_list')
            ->willReturn('/census/list');

        $response = ($this->controller)($request, 'uuid-999');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('error');
        $this->assertContains('census.error.not_found', $flashes);
    }

    public function testItRendersEditFormOnGetRequest(): void
    {
        $request = $this->createRequest('GET');

        $mockResponse = $this->createMock(GetCensusResponse::class);
        $this->getHandler->method('handle')->willReturn($mockResponse);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('backoffice/census/edit.html.twig', ['census' => $mockResponse])
            ->willReturn('<html>Edit Form</html>');

        $response = ($this->controller)($request, 'uuid-123');

        $this->assertEquals('<html>Edit Form</html>', $response->getContent());
    }

    public function testItUpdatesCensusAndRedirectsOnSuccess(): void
    {
        $request = $this->createRequest('POST');
        $request->request->set('_csrf_token', 'valid-token');

        $mockResponse = $this->createMock(GetCensusResponse::class);
        $this->getHandler->method('handle')->willReturn($mockResponse);
        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);

        $this->updateHandler->expects($this->once())->method('handle');

        $this->router->expects($this->once())
            ->method('generate')
            ->with('backoffice_census_list')
            ->willReturn('/census/list');

        $response = ($this->controller)($request, 'uuid-123');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('success');
        $this->assertContains('census.flash.updated', $flashes);
    }
}
