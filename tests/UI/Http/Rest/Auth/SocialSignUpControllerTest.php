<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Auth;

use App\Auth\Application\SignUp\SignUpHandler;
use App\Auth\Application\SignUp\SignUpResponse;
use App\UI\Http\Rest\Auth\SocialSignUpController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SocialSignUpControllerTest extends TestCase
{
    private SignUpHandler&MockObject $handler;
    private SocialSignUpController   $controller;

    protected function setUp(): void
    {
        $this->handler    = $this->createMock(SignUpHandler::class);
        $this->controller = new SocialSignUpController($this->handler);
    }

    public function test_invokes_handler_and_returns_response_on_valid_request(): void
    {
        $this->handler->expects(self::once())
            ->method('handle')
            ->willReturn(new SignUpResponse('access-token', 'refresh-token'));

        $response = ($this->controller)(
            $this->makeRequest(['id_token' => 'tok', 'name' => 'Alice', 'phone' => '+34600000000']),
            'google',
        );

        self::assertSame('access-token', $response->accessToken);
        self::assertSame('refresh-token', $response->refreshToken);
    }

    public function test_throws_when_id_token_is_missing(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(\InvalidArgumentException::class);

        ($this->controller)(
            $this->makeRequest(['name' => 'Alice', 'phone' => '+34600000000']),
            'google',
        );
    }

    public function test_throws_when_name_is_missing(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(\InvalidArgumentException::class);

        ($this->controller)(
            $this->makeRequest(['id_token' => 'tok', 'phone' => '+34600000000']),
            'google',
        );
    }

    public function test_throws_when_phone_is_missing(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->expectException(\InvalidArgumentException::class);

        ($this->controller)(
            $this->makeRequest(['id_token' => 'tok', 'name' => 'Alice']),
            'google',
        );
    }

    private function makeRequest(array $body): Request
    {
        return Request::create('/api/auth/google/signup', 'POST', [], [], [], [], json_encode($body));
    }
}
