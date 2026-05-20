<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\DeleteBackofficeUser;

use App\Auth\Application\DeleteBackofficeUser\DeleteBackofficeUserHandler;
use App\Auth\Application\DeleteBackofficeUser\DeleteBackofficeUserRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteBackofficeUserHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private DeleteBackofficeUserHandler            $handler;

    protected function setUp(): void
    {
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new DeleteBackofficeUserHandler($this->userRepository, $this->transactionManager);
    }

    public function test_handle_deletes_user_successfully(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal());

        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository
            ->expects(self::once())
            ->method('deleteRefreshTokensByEmail')
            ->with(self::callback(fn(Email $e) => $e->value() === 'admin@example.com'));
        $this->userRepository->expects(self::once())->method('delete')->with($user);
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new DeleteBackofficeUserRequest(self::UUID));

        self::assertSame(self::UUID, $response->userId);
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle(new DeleteBackofficeUserRequest(self::UUID));
    }

    public function test_handle_rolls_back_on_delete_exception(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal());

        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository->method('delete')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new DeleteBackofficeUserRequest(self::UUID));
    }
}
