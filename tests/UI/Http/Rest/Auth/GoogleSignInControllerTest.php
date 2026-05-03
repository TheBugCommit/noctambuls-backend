<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Auth;

use App\Auth\Application\SignIn\SignInHandlerInterface;
use App\Auth\Application\SignIn\SignInRequest;
use App\Auth\Application\SignIn\SignInResponse;
use App\UI\Http\Rest\Auth\SocialSignInController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class GoogleSignInControllerTest extends TestCase
{
    private SignInHandlerInterface&MockObject $handler;
    private SocialSignInController   $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(SignInHandlerInterface::class);
        $this->controller = new SocialSignInController($this->handler);
    }

    public function test_invoke_calls_handler_with_token_from_body(): void
    {
        $expectedResponse = new SignInResponse('jwt.token', 'refresh.token');

        $this->handler
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(fn(SignInRequest $req) => $req->externalToken === 'google-id-token-value'
                && $req->providerType === 'google'))
            ->willReturn($expectedResponse);

        $request = $this->makeRequest(['id_token' => 'google-id-token-value']);

        $response = ($this->controller)($request, 'google');

        self::assertSame($expectedResponse, $response);
    }

    public function test_invoke_returns_sign_in_response(): void
    {
        $signInResponse = new SignInResponse('access.jwt', 'refresh.token');

        $this->handler->method('handle')->willReturn($signInResponse);

        $request  = $this->makeRequest(['id_token' => 'some-token']);
        $response = ($this->controller)($request, 'google');

        self::assertSame('access.jwt', $response->accessToken);
        self::assertSame('refresh.token', $response->refreshToken);
    }

    public function test_invoke_throws_when_id_token_missing(): void
    {
        $this->handler->expects(self::never())->method('handle');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing token field.');

        ($this->controller)($this->makeRequest([]), 'google');
    }

    public function test_invoke_throws_when_id_token_is_empty_string(): void
    {
        $this->handler->expects(self::never())->method('handle');

        $this->expectException(\InvalidArgumentException::class);

        ($this->controller)($this->makeRequest(['id_token' => '']), 'google');
    }

    public function test_invoke_throws_when_body_is_not_json(): void
    {
        $this->handler->expects(self::never())->method('handle');

        $this->expectException(\InvalidArgumentException::class);

        $request = Request::create('/api/auth/google', 'POST', [], [], [], [], 'not-valid-json');
        ($this->controller)($request, 'google');
    }

    public function test_invoke_throws_when_id_token_is_not_string(): void
    {
        $this->handler->expects(self::never())->method('handle');

        $this->expectException(\InvalidArgumentException::class);

        ($this->controller)($this->makeRequest(['id_token' => 12345]), 'google');
    }

    private function makeRequest(array $body): Request
    {
        return Request::create(
            '/api/auth/google',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );
    }
}
