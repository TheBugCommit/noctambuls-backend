<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Jwt;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Jwt\JwtCreatedListener;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class JwtCreatedListenerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private JwtCreatedListener $listener;

    protected function setUp(): void
    {
        $this->listener = new JwtCreatedListener();
    }

    public function test_invoke_adds_user_id_to_payload(): void
    {
        $authUser = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $adapter  = new AuthUserSymfonyAdapter($authUser);

        /** @var JWTCreatedEvent&MockObject $event */
        $event = $this->createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn($adapter);
        $event->method('getData')->willReturn(['username' => 'user@example.com']);
        $event->expects(self::once())
            ->method('setData')
            ->with(self::callback(fn(array $payload) => $payload['id'] === self::UUID));

        ($this->listener)($event);
    }

    public function test_invoke_does_nothing_for_non_adapter_user(): void
    {
        $user = $this->createMock(UserInterface::class);

        /** @var JWTCreatedEvent&MockObject $event */
        $event = $this->createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn($user);
        $event->expects(self::never())->method('setData');

        ($this->listener)($event);
    }
}
