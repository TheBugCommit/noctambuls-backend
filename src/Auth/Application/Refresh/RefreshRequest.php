<?php
namespace App\Auth\Application\Refresh;

readonly class RefreshRequest
{
    public function __construct(public string $refreshToken) {}
}
