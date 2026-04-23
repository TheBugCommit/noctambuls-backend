<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Auth;

use App\Auth\Application\SignIn\SignInHandlerInterface;
use App\Auth\Application\SignIn\SignInRequest;
use App\Auth\Application\SignIn\SignInResponse;
use App\Auth\Domain\ValueObject\Provider;
use Symfony\Component\HttpFoundation\Request;

final readonly class GoogleSignInController
{
    public function __construct(
        private SignInHandlerInterface $handler,
    )
    {
    }

    public function __invoke(Request $request): SignInResponse
    {
        $body = json_decode($request->getContent(), true);
        $token = is_array($body) ? ($body['id_token'] ?? null) : null;

        if (!is_string($token) || $token === '') {
            throw new \InvalidArgumentException('Missing token field.');
        }

        return $this->handler->handle(new SignInRequest($token, Provider::google()->value()));
    }
}
