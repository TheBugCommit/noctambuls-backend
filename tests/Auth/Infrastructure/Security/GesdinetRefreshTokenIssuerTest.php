<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Security\GesdinetRefreshTokenIssuer;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GesdinetRefreshTokenIssuerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const int   TTL  = 2592000;

    private RefreshTokenGeneratorInterface&MockObject $generator;
    private RefreshTokenManagerInterface&MockObject   $manager;
    private GesdinetRefreshTokenIssuer                $issuer;

    protected function setUp(): void
    {
        $this->generator = $this->createMock(RefreshTokenGeneratorInterface::class);
        $this->manager   = $this->createMock(RefreshTokenManagerInterface::class);
        $this->issuer    = new GesdinetRefreshTokenIssuer($this->generator, $this->manager, self::TTL);
    }

    public function test_issue_token_returns_refresh_token_string(): void
    {
        $user  = $this->makeUser();
        $token = $this->makeRefreshTokenMock('my-refresh-token');

        $this->generator->method('createForUserWithTtl')->willReturn($token);
        $this->manager->expects(self::once())->method('save')->with($token);

        $result = $this->issuer->issueTokenForUser($user);

        self::assertSame('my-refresh-token', $result);
    }

    public function test_issue_token_passes_ttl_to_generator(): void
    {
        $user  = $this->makeUser();
        $token = $this->makeRefreshTokenMock('token');

        $this->generator
            ->expects(self::once())
            ->method('createForUserWithTtl')
            ->with(self::anything(), self::TTL)
            ->willReturn($token);

        $this->issuer->issueTokenForUser($user);
    }

    public function test_issue_token_saves_generated_token(): void
    {
        $user  = $this->makeUser();
        $token = $this->makeRefreshTokenMock('token');

        $this->generator->method('createForUserWithTtl')->willReturn($token);
        $this->manager->expects(self::once())->method('save');

        $this->issuer->issueTokenForUser($user);
    }

    private function makeUser(): AuthUser
    {
        return AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
        );
    }

    private function makeRefreshTokenMock(string $tokenString): RefreshTokenInterface&MockObject
    {
        $mock = $this->createMock(RefreshTokenInterface::class);
        $mock->method('getRefreshToken')->willReturn($tokenString);

        return $mock;
    }
}
