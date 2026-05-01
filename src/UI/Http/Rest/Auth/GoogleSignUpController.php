<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Auth;

use App\Auth\Application\SignUp\SignUpHandler;
use App\Auth\Application\SignUp\SignUpRequest;
use App\Auth\Application\SignUp\SignUpResponse;
use App\Auth\Domain\ValueObject\Provider;
use Symfony\Component\HttpFoundation\Request;

final readonly class GoogleSignUpController
{
    public function __construct(
        private SignUpHandler $handler,
    ) {
    }

    public function __invoke(Request $request): SignUpResponse
    {
        $body = json_decode($request->getContent(), true);

        $token = is_array($body) ? ($body['id_token'] ?? null) : null;
        $name  = is_array($body) ? ($body['name'] ?? null) : null;
        $phone = is_array($body) ? ($body['phone'] ?? null) : null;

        if (!is_string($token) || $token === '') {
            throw new \InvalidArgumentException('Missing id_token field.');
        }

        if (!is_string($name) || $name === '') {
            throw new \InvalidArgumentException('Missing name field.');
        }

        if (!is_string($phone) || $phone === '') {
            throw new \InvalidArgumentException('Missing phone field.');
        }

        return $this->handler->handle(new SignUpRequest($token, Provider::google()->value(), $name, $phone));
    }
}
