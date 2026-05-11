<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Backoffice\Census;

use App\Census\Application\PublishCensus\PublishCensusHandler;
use App\Census\Domain\Exception\CensusNotEditableException;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\UI\Http\Backoffice\Census\CensusPublishController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CensusPublishControllerTest extends TestCase
{
    private PublishCensusHandler&MockObject $handler;
    private CensusPublishController $controller;
    private RouterInterface&MockObject $router;
    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(PublishCensusHandler::class);
        $this->controller = new CensusPublishController($this->handler);

        $this->router = $this->createMock(RouterInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->requestStack = new RequestStack();

        $container = new Container();
        $container->set('router', $this->router);
        $container->set('security.csrf.token_manager', $this->csrfTokenManager);
        $container->set('request_stack', $this->requestStack);

        $this->controller->setContainer($container);
    }

    private function createRequest(string $csrfToken = 'valid-token'): Request
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('_csrf_token', $csrfToken);
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);

        return $request;
    }

    public function testItRedirectsIfCsrfIsInvalid(): void
    {
        $request = $this->createRequest('invalid-token');
        $this->csrfTokenManager->method('isTokenValid')->willReturn(false);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('backoffice_census_list')
            ->willReturn('/census/list');

        $response = ($this->controller)($request, 'uuid-123');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('error');
        $this->assertContains('census.error.invalid_csrf', $flashes);
    }

    public function testItPublishesCensusAndRedirectsOnSuccess(): void
    {
        $request = $this->createRequest();
        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);

        $this->handler->expects($this->once())->method('handle');

        $this->router->expects($this->once())->method('generate')->willReturn('/census/list');

        $response = ($this->controller)($request, 'uuid-123');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('success');
        $this->assertContains('census.flash.published', $flashes);
    }

    public function testItCatchesDomainExceptionsAndRedirectsWithError(): void
    {
        $request = $this->createRequest();
        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);

        // Simulem que el cens no existeix
        $this->handler->method('handle')->willThrowException(new CensusNotFoundException());
        $this->router->expects($this->once())->method('generate')->willReturn('/census/list');

        $response = ($this->controller)($request, 'uuid-999');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $flashes = $request->getSession()->getFlashBag()->get('error');
        $this->assertContains('census.error.not_found', $flashes);
    }
}
