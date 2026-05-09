<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Auth;

use App\Auth\Application\AnonymizeUser\AnonymizeUserHandler;
use App\Auth\Application\AnonymizeUser\AnonymizeUserRequest;
use App\Auth\Infrastructure\Security\JwtAuthenticatedUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class SelfDeleteAccountController
{
    public function __construct(
        private AnonymizeUserHandler $handler,
        private Security             $security,
    ) {
    }

    public function __invoke(): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof JwtAuthenticatedUserInterface) {
            throw new AccessDeniedHttpException();
        }

        $this->handler->handle(new AnonymizeUserRequest($user->getAuthUserId()));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
