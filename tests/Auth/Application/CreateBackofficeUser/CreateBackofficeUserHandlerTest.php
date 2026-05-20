<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\CreateBackofficeUser;

use App\Auth\Application\CreateBackofficeUser\CreateBackofficeUserHandler;
use App\Auth\Application\CreateBackofficeUser\CreateBackofficeUserRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateBackofficeUserHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject     $passwordHasher;
    private UuidGeneratorInterface&MockObject      $uuidGenerator;
    private TransactionManagerInterface&MockObject $transactionManager;
    private CreateBackofficeUserHandler            $handler;

    protected function setUp(): void
    {
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->passwordHasher     = $this->createMock(PasswordHasherInterface::class);
        $this->uuidGenerator      = $this->createMock(UuidGeneratorInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new CreateBackofficeUserHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->uuidGenerator,
            $this->transactionManager,
        );
    }

    public function test_handle_creates_user_successfully(): void
    {
        $bcryptHash = password_hash('secret', PASSWORD_BCRYPT);

        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->passwordHasher->method('hash')->willReturn($bcryptHash);
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');
        $this->userRepository->expects(self::once())->method('save');

        $response = $this->handler->handle(new CreateBackofficeUserRequest('admin@example.com', 'ROLE_ADMIN', 'ValidPass1!'));

        self::assertSame(self::UUID, $response->userId);
    }

    public function test_handle_throws_when_email_already_exists(): void
    {
        $existingUser = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal());
        $this->userRepository->method('findByEmailAndProvider')->willReturn($existingUser);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserAlreadyExistsException::class);

        $this->handler->handle(new CreateBackofficeUserRequest('admin@example.com', 'ROLE_ADMIN', 'ValidPass1!'));
    }

    public function test_handle_throws_on_user_role(): void
    {
        $bcryptHash = password_hash('secret', PASSWORD_BCRYPT);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->passwordHasher->method('hash')->willReturn($bcryptHash);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Backoffice can't have role USER.");

        $this->handler->handle(new CreateBackofficeUserRequest('user@example.com', 'ROLE_USER', 'ValidPass1!'));
    }

    public function test_handle_throws_on_invalid_plain_password(): void
    {
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters.');

        $this->handler->handle(new CreateBackofficeUserRequest('admin@example.com', 'ROLE_ADMIN', 'short'));
    }

    public function test_handle_rolls_back_on_repository_exception(): void
    {
        $bcryptHash = password_hash('secret', PASSWORD_BCRYPT);
        $this->uuidGenerator->method('generate')->willReturn(self::UUID);
        $this->userRepository->method('findByEmailAndProvider')->willReturn(null);
        $this->passwordHasher->method('hash')->willReturn($bcryptHash);
        $this->userRepository->method('save')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new CreateBackofficeUserRequest('admin@example.com', 'ROLE_ADMIN', 'ValidPass1!'));
    }
}
