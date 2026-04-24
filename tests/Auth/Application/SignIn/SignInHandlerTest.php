<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\SignIn;

use App\Auth\Application\SignIn\RefreshTokenIssuerInterface;
use App\Auth\Application\SignIn\SignInHandler;
use App\Auth\Application\SignIn\SignInRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\InvalidExternalTokenException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SignInHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private ExternalTokenValidatorInterface&MockObject $tokenValidator;
    private AuthUserRepositoryInterface&MockObject     $userRepository;
    private JwtTokenGeneratorInterface&MockObject      $jwtGenerator;
    private TransactionManagerInterface&MockObject     $transactionManager;
    private UuidGeneratorInterface&MockObject          $uuidGenerator;
    private RefreshTokenIssuerInterface&MockObject     $refreshTokenIssuer;
    private SignInHandler                              $handler;

    protected function setUp(): void
    {
        $this->tokenValidator     = $this->createMock(ExternalTokenValidatorInterface::class);
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->jwtGenerator       = $this->createMock(JwtTokenGeneratorInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->uuidGenerator      = $this->createMock(UuidGeneratorInterface::class);
        $this->refreshTokenIssuer = $this->createMock(RefreshTokenIssuerInterface::class);

        $this->handler = new SignInHandler(
            $this->tokenValidator,
            $this->userRepository,
            $this->jwtGenerator,
            $this->transactionManager,
            $this->uuidGenerator,
            $this->refreshTokenIssuer,
        );
    }

    public function test_handle_creates_new_user_when_not_found(): void
    {
        $email = new Email('new@example.com');

        $this->tokenValidator->method('validate')->willReturn($email);
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->jwtGenerator->method('generate')->willReturn('jwt.token.here');
        $this->refreshTokenIssuer->method('issueTokenForUser')->willReturn('refresh-token-string');

        $this->userRepository->expects(self::once())->method('save');
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new SignInRequest('google-id-token', Provider::google()->value()));

        self::assertSame('jwt.token.here', $response->accessToken);
        self::assertSame('refresh-token-string', $response->refreshToken);
    }

    public function test_handle_reuses_existing_user(): void
    {
        $email       = new Email('existing@example.com');
        $existingUser = AuthUser::create(
            new AuthUserId(self::UUID),
            $email,
            Provider::google(),
        );

        $this->tokenValidator->method('validate')->willReturn($email);
        $this->userRepository->method('findByEmailAndProvider')->willReturn($existingUser);
        $this->jwtGenerator->method('generate')->willReturn('jwt.for.existing');
        $this->refreshTokenIssuer->method('issueTokenForUser')->willReturn('refresh-for-existing');

        $this->userRepository->expects(self::never())->method('save');

        $response = $this->handler->handle(new SignInRequest('google-id-token', 'google'));

        self::assertSame('jwt.for.existing', $response->accessToken);
        self::assertSame('refresh-for-existing', $response->refreshToken);
    }

    public function test_handle_rolls_back_when_exception_is_thrown(): void
    {
        $this->tokenValidator->method('validate')->willReturn(new Email('user@example.com'));
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('save')->willThrowException(new \RuntimeException('DB error'));

        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new SignInRequest('token', 'google'));
    }

    public function test_handle_throws_when_external_token_invalid(): void
    {
        $this->tokenValidator
            ->method('validate')
            ->willThrowException(new InvalidExternalTokenException());

        $this->transactionManager->expects(self::never())->method('begin');
        $this->userRepository->expects(self::never())->method('save');

        $this->expectException(InvalidExternalTokenException::class);

        $this->handler->handle(new SignInRequest('bad-token', 'google'));
    }

    public function test_handle_passes_correct_provider_to_repository(): void
    {
        $email = new Email('user@example.com');

        $this->tokenValidator->method('validate')->willReturn($email);
        $this->jwtGenerator->method('generate')->willReturn('token');
        $this->refreshTokenIssuer->method('issueTokenForUser')->willReturn('refresh');
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmailAndProvider')
            ->with(
                self::equalTo($email),
                self::callback(fn(Provider $p) => $p->value() === 'google'),
            )
            ->willReturn(null);

        $this->userRepository->method('save');

        $this->handler->handle(new SignInRequest('token', 'google'));
    }
}
