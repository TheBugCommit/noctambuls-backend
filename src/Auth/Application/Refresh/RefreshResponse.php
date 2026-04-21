<?php
namespace App\Auth\Application\Refresh;

readonly class RefreshResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType = 'Bearer'
    ) {}
}
