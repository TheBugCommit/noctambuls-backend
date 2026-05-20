<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\ListAppUsers;

use App\Auth\Application\ListAppUsers\ListAppUsersHandler;
use App\Auth\Application\ListAppUsers\ListAppUsersRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListAppUsersHandlerTest extends TestCase
{
    private const string UUID_1 = '550e8400-e29b-41d4-a716-446655440001';
    private const string UUID_2 = '550e8400-e29b-41d4-a716-446655440002';

    private AuthUserRepositoryInterface&MockObject $authUserRepository;
    private ProfileRepositoryInterface&MockObject  $profileRepository;
    private ListAppUsersHandler                    $handler;

    protected function setUp(): void
    {
        $this->authUserRepository = $this->createMock(AuthUserRepositoryInterface::class);
        $this->profileRepository  = $this->createMock(ProfileRepositoryInterface::class);

        $this->handler = new ListAppUsersHandler($this->authUserRepository, $this->profileRepository);
    }

    public function test_handle_returns_paginated_users(): void
    {
        $users = [
            AuthUser::create(new AuthUserId(self::UUID_1), new Email('user1@example.com'), Provider::google(), Role::USER),
            AuthUser::create(new AuthUserId(self::UUID_2), new Email('user2@example.com'), Provider::google(), Role::USER),
        ];

        $this->authUserRepository->method('findPaginatedByRole')->willReturn($users);
        $this->authUserRepository->method('countByRole')->willReturn(5);
        $this->profileRepository->method('findByIds')->willReturn([]);

        $response = $this->handler->handle(new ListAppUsersRequest(1, 20));

        self::assertSame(5, $response->total);
        self::assertSame(1, $response->page);
        self::assertSame(20, $response->limit);
        self::assertCount(2, $response->users);
    }

    public function test_handle_returns_empty_list(): void
    {
        $this->authUserRepository->method('findPaginatedByRole')->willReturn([]);
        $this->authUserRepository->method('countByRole')->willReturn(0);
        $this->profileRepository->method('findByIds')->willReturn([]);

        $response = $this->handler->handle(new ListAppUsersRequest());

        self::assertSame(0, $response->total);
        self::assertSame([], $response->users);
    }

    public function test_handle_passes_correct_pagination_params(): void
    {
        $this->authUserRepository
            ->expects(self::once())
            ->method('findPaginatedByRole')
            ->with(
                Role::USER,
                self::callback(fn(int $page) => $page === 3),
                self::callback(fn(int $limit) => $limit === 10),
            )
            ->willReturn([]);

        $this->authUserRepository->method('countByRole')->willReturn(0);
        $this->profileRepository->method('findByIds')->willReturn([]);

        $this->handler->handle(new ListAppUsersRequest(3, 10));
    }
}
