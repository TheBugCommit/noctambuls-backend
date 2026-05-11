<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Backoffice\Census;

use App\Census\Application\CreateCensus\CreateCensusHandler;
use App\Census\Domain\Exception\EmptyCoordinateException;
use App\UI\Http\Backoffice\Census\CensusCreateController;
use InvalidArgumentException;
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

class CensusCreateControllerTest extends TestCase
{
    private RequestStack $requestStack;
    private CreateCensusHandler&MockObject $handler;
    private CensusCreateController $controller;
    private RouterInterface&MockObject $router;
    private Environment&MockObject $twig;
    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(CreateCensusHandler::class);
        $this->controller = new CensusCreateController($this->handler);

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

    public function testItRendersCreateFormOnGetRequest(): void
    {
        $request = $this->createRequest('GET');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('backoffice/census/create.html.twig')
            ->willReturn('<html>Formulari</html>');

        $response = ($this->controller)($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('<html>Formulari</html>', $response->getContent());
    }

    public function testItRedirectsBackIfCsrfIsInvalid(): void
    {
        $request = $this->createRequest('POST');
        $request->request->set('_csrf_token', 'invalid-token');

        $this->csrfTokenManager->method('isTokenValid')->willReturn(false);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('backoffice_census_create')
            ->willReturn('/census/create');

        $response = ($this->controller)($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/census/create', $response->getTargetUrl());

        $flashes = $request->getSession()->getFlashBag()->get('error');
        $this->assertContains('census.error.invalid_csrf', $flashes);
    }

    public function testItCreatesCensusAndRedirectsOnSuccess(): void
    {
        $request = $this->createRequest('POST');
        $request->request->set('_csrf_token', 'valid-token');
        $request->request->set('title', 'Títol Valid');

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);

        $this->handler->expects($this->once())->method('handle');

        $this->router->expects($this->once())
            ->method('generate')
            ->with('backoffice_census_list')
            ->willReturn('/census/list');

        $response = ($this->controller)($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/census/list', $response->getTargetUrl());

        $flashes = $request->getSession()->getFlashBag()->get('success');
        $this->assertContains('census.flash.created', $flashes);
    }

    public function testItCatchesDomainExceptionsAndRendersFormWithErrors(): void
    {
        $request = $this->createRequest('POST');
        $request->request->set('_csrf_token', 'valid-token');
        $request->request->set('title', '');

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);

        // Fem que el handler llenci l'excepció de domini
        $this->handler->method('handle')
            ->willThrowException(new InvalidArgumentException('Title cannot be empty'));

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'backoffice/census/create.html.twig',
                $this->callback(fn($params) => isset($params['data']))
            )
            ->willReturn('<html>Formulari amb errors</html>');

        $response = ($this->controller)($request);

        $this->assertInstanceOf(Response::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('error');
        $this->assertContains('Title cannot be empty', $flashes);
    }
}
