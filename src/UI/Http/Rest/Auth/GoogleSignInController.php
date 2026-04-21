<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Auth;

use App\Auth\Application\SignIn\SignInHandler;
use App\Auth\Application\SignIn\SignInRequest;
use App\Auth\Application\SignIn\SignInResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class GoogleSignInController
{
    public function __construct(private SignInHandler $handler)
    {
    }

    public function __invoke(Request $request): SignInResponse
    {
        $body = json_decode($request->getContent(), true);
        $token = $body['token'] ?? null;

        if (!is_string($token) || $token === '') {
            throw new \InvalidArgumentException('Missing token field.');
        }

        return $this->handler->handle(new SignInRequest($token, 'google'));
    }
}
