<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\SignUp;

use App\Auth\Application\SignIn\RefreshTokenIssuerInterface;
use App\Auth\Application\SignUp\SignUpHandler;
use App\Auth\Application\SignUp\SignUpRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Profile\Application\Service\CreateUserProfileService;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SignUpHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private ExternalTokenValidatorInterface&MockObject $tokenValidator;
    private AuthUserRepositoryInterface&MockObject     $userRepository;
    private JwtTokenGeneratorInterface&MockObject      $jwtGenerator;
    private RefreshTokenIssuerInterface&MockObject     $refreshTokenIssuer;
    private TransactionManagerInterface&MockObject     $transactionManager;
    private UuidGeneratorInterface&MockObject          $uuidGenerator;
    private CreateUserProfileService&MockObject        $createUserProfileService;
    private SignUpHandler                              $handler;

    protected function setUp(): void
    {
        $this->tokenValidator           = $this->createMock(ExternalTokenValidatorInterface::class);
        $this->userRepository           = $this->createMock(AuthUserRepositoryInterface::class);
        $this->jwtGenerator             = $this->createMock(JwtTokenGeneratorInterface::class);
        $this->refreshTokenIssuer       = $this->createMock(RefreshTokenIssuerInterface::class);
        $this->transactionManager       = $this->createMock(TransactionManagerInterface::class);
        $this->uuidGenerator            = $this->createMock(UuidGeneratorInterface::class);
        $this->createUserProfileService = $this->createMock(CreateUserProfileService::class);

        $this->handler = new SignUpHandler(
            $this->tokenValidator,
            $this->userRepository,
            $this->jwtGenerator,
            $this->refreshTokenIssuer,
            $this->transactionManager,
            $this->uuidGenerator,
            $this->createUserProfileService,
        );
    }

    public function test_handle_creates_user_and_returns_tokens(): void
    {
        $this->tokenValidator->method('validate')->willReturn(new Email('user@example.com'));
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->jwtGenerator->method('generate')->willReturn('access-token');
        $this->refreshTokenIssuer->method('issueTokenForUser')->willReturn('refresh-token');
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');
        $this->userRepository->expects(self::once())->method('save');
        $this->createUserProfileService->expects(self::once())->method('execute')->with(self::UUID, 'John', '123456789');

        $response = $this->handler->handle(new SignUpRequest('ext-token', 'google', 'John', '123456789'));

        self::assertSame('access-token', $response->accessToken);
        self::assertSame('refresh-token', $response->refreshToken);
    }

    public function test_handle_throws_when_user_already_exists(): void
    {
        $existingUser = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $this->tokenValidator->method('validate')->willReturn(new Email('user@example.com'));
        $this->userRepository->method('findByEmailAndProvider')->willReturn($existingUser);
        $this->transactionManager->expects(self::never())->method('begin');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserAlreadyExistsException::class);

        $this->handler->handle(new SignUpRequest('ext-token', 'google', 'John', '123456789'));
    }

    public function test_handle_rolls_back_on_repository_save_exception(): void
    {
        $this->tokenValidator->method('validate')->willReturn(new Email('user@example.com'));
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('save')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new SignUpRequest('ext-token', 'google', 'John', '123456789'));
    }

    public function test_handle_rolls_back_on_jwt_generator_exception(): void
    {
        $this->tokenValidator->method('validate')->willReturn(new Email('user@example.com'));
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->jwtGenerator->method('generate')->willThrowException(new \RuntimeException('JWT error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new SignUpRequest('ext-token', 'google', 'John', '123456789'));
    }

    public function test_handle_validates_provider_type_creates_correct_provider(): void
    {
        $this->tokenValidator
            ->expects(self::once())
            ->method('validate')
            ->with(
                'ext-token',
                self::callback(fn(Provider $p) => $p->value() === 'google')
            )
            ->willReturn(new Email('user@example.com'));

        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->jwtGenerator->method('generate')->willReturn('at');
        $this->refreshTokenIssuer->method('issueTokenForUser')->willReturn('rt');

        $this->handler->handle(new SignUpRequest('ext-token', 'google', 'John', '123456789'));
    }
}
