<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Auth\Infrastructure\Security\BlockedUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

final class BlockedUserCheckerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private BlockedUserChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new BlockedUserChecker();
    }

    public function test_checkPreAuth_throws_when_user_is_blocked(): void
    {
        $authUser = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $authUser->block();
        $adapter = new AuthUserSymfonyAdapter($authUser);

        $this->expectException(DisabledException::class);

        $this->checker->checkPreAuth($adapter);
    }

    public function test_checkPreAuth_passes_when_user_is_not_blocked(): void
    {
        $authUser = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $adapter  = new AuthUserSymfonyAdapter($authUser);

        $this->checker->checkPreAuth($adapter);

        $this->addToAssertionCount(1);
    }

    public function test_checkPreAuth_ignores_non_adapter_user(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function test_checkPostAuth_does_nothing(): void
    {
        $authUser = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $adapter  = new AuthUserSymfonyAdapter($authUser);

        $this->checker->checkPostAuth($adapter);

        $this->addToAssertionCount(1);
    }
}
