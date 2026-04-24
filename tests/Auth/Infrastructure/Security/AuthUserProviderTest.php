<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Security\AuthUserProvider;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class AuthUserProviderTest extends TestCase
{
    private const string UUID  = '550e8400-e29b-41d4-a716-446655440000';
    private const string EMAIL = 'user@example.com';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private AuthUserProvider                       $provider;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(AuthUserRepositoryInterface::class);
        $this->provider       = new AuthUserProvider($this->userRepository);
    }

    public function test_load_user_by_identifier_returns_adapter(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email(self::EMAIL),
            Provider::google(),
        );

        $this->userRepository->method('findByEmail')->willReturn($user);

        $result = $this->provider->loadUserByIdentifier(self::EMAIL);

        self::assertInstanceOf(AuthUserSymfonyAdapter::class, $result);
        self::assertSame(self::EMAIL, $result->getUserIdentifier());
    }

    public function test_load_user_by_identifier_throws_when_not_found(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->provider->loadUserByIdentifier(self::EMAIL);
    }

    public function test_refresh_user_reloads_from_repository(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email(self::EMAIL),
            Provider::google(),
        );

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->willReturn($user);

        $existingAdapter = new AuthUserSymfonyAdapter($user);
        $refreshed       = $this->provider->refreshUser($existingAdapter);

        self::assertInstanceOf(AuthUserSymfonyAdapter::class, $refreshed);
        self::assertSame(self::EMAIL, $refreshed->getUserIdentifier());
    }

    public function test_supports_class_returns_true_for_adapter(): void
    {
        self::assertTrue($this->provider->supportsClass(AuthUserSymfonyAdapter::class));
    }

    public function test_supports_class_returns_false_for_other(): void
    {
        self::assertFalse($this->provider->supportsClass(\stdClass::class));
    }

    public function test_load_user_passes_email_to_repository(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with(self::callback(fn(Email $e) => $e->value() === self::EMAIL))
            ->willReturn(null);

        try {
            $this->provider->loadUserByIdentifier(self::EMAIL);
        } catch (UserNotFoundException) {
            // expected
        }
    }
}
