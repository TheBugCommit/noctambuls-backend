<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Auth;

use App\Auth\Application\Refresh\RefreshHandler;
use App\Auth\Application\Refresh\RefreshRequest;
use App\Auth\Application\Refresh\RefreshResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class RefreshTokenController
{
    public function __construct(private RefreshHandler $handler) {}

    public function __invoke(Request $request): RefreshResponse
    {
        $body = json_decode($request->getContent(), true);
        $refreshToken = $body['refresh_token'] ?? null;

        if (!is_string($refreshToken) || $refreshToken === '') {
            throw new \InvalidArgumentException('Missing refresh_token field.');
        }

        return $this->handler->handle(new RefreshRequest($refreshToken));
    }
}
