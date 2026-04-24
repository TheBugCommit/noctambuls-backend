<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Jwt;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Jwt\LexikJwtTokenGenerator;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LexikJwtTokenGeneratorTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private JWTTokenManagerInterface&MockObject $jwtManager;
    private LexikJwtTokenGenerator             $generator;

    protected function setUp(): void
    {
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->generator  = new LexikJwtTokenGenerator($this->jwtManager);
    }

    public function test_generate_returns_jwt_string(): void
    {
        $user = $this->makeUser();

        $this->jwtManager->method('create')->willReturn('jwt.header.payload');

        $result = $this->generator->generate($user);

        self::assertSame('jwt.header.payload', $result);
    }

    public function test_generate_passes_adapter_to_jwt_manager(): void
    {
        $user = $this->makeUser();

        $this->jwtManager
            ->expects(self::once())
            ->method('create')
            ->with(self::isInstanceOf(AuthUserSymfonyAdapter::class))
            ->willReturn('token');

        $this->generator->generate($user);
    }

    public function test_generate_adapter_has_correct_identifier(): void
    {
        $user = $this->makeUser();

        $this->jwtManager
            ->expects(self::once())
            ->method('create')
            ->with(self::callback(function (AuthUserSymfonyAdapter $adapter) {
                return $adapter->getUserIdentifier() === 'user@example.com';
            }))
            ->willReturn('token');

        $this->generator->generate($user);
    }

    private function makeUser(): AuthUser
    {
        return AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
        );
    }
}
