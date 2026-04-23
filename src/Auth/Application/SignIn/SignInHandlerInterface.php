<?php

declare(strict_types=1);

namespace App\Auth\Application\SignIn;

interface SignInHandlerInterface
{
    public function handle(SignInRequest $request): SignInResponse;
}
