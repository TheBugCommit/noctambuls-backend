<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Auth;

use App\Auth\Application\AnonymizeUser\AnonymizeUserHandler;
use App\Auth\Infrastructure\Security\JwtAuthenticatedUserInterface;
use App\UI\Http\Rest\Auth\SelfDeleteAccountController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SelfDeleteAccountControllerTest extends TestCase
{
    private AnonymizeUserHandler&MockObject $handler;
    private Security&MockObject             $security;
    private SelfDeleteAccountController     $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(AnonymizeUserHandler::class);
        $this->security   = $this->createMock(Security::class);
        $this->controller = new SelfDeleteAccountController($this->handler, $this->security);
    }

    public function test_returns_204_when_user_is_jwt_authenticated(): void
    {
        $user = new class implements JwtAuthenticatedUserInterface {
            public function getAuthUserId(): string { return '550e8400-e29b-41d4-a716-446655440001'; }
            public function getRoles(): array { return ['ROLE_USER']; }
            public function eraseCredentials(): void {}
            public function getUserIdentifier(): string { return 'test@test.com'; }
        };

        $this->security->method('getUser')->willReturn($user);
        $this->handler->expects(self::once())->method('handle');

        $response = ($this->controller)();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function test_throws_access_denied_when_user_is_not_jwt_authenticated(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(AccessDeniedHttpException::class);

        ($this->controller)();
    }
}
