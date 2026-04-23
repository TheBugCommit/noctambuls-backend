<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Jwt;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final readonly class JwtCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $userAdapter = $event->getUser();
        if (!$userAdapter instanceof AuthUserSymfonyAdapter) {
            return;
        }

        $authUser = $userAdapter->getAuthUser();

        $payload = $event->getData();

        $payload['id'] = $authUser->id()->value();

        $event->setData($payload);
    }
}
