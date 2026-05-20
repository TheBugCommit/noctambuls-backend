<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UpdateNotificationTemplate;

use App\Notification\Application\UpdateNotificationTemplate\UpdateNotificationTemplateHandler;
use App\Notification\Application\UpdateNotificationTemplate\UpdateNotificationTemplateRequest;
use App\Notification\Domain\Entity\NotificationTemplate;
use App\Notification\Domain\Exception\NotificationTemplateNotFoundException;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateNotificationTemplateHandlerTest extends TestCase
{
    private NotificationTemplateRepositoryInterface&MockObject $repository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private UpdateNotificationTemplateHandler $handler;

    protected function setUp(): void
    {
        $this->repository         = $this->createMock(NotificationTemplateRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->handler            = new UpdateNotificationTemplateHandler($this->repository, $this->transactionManager);
    }

    public function test_throws_not_found_when_template_does_not_exist(): void
    {
        $this->repository->method('findByEvent')->willReturn(null);

        $this->expectException(NotificationTemplateNotFoundException::class);

        $this->handler->handle(new UpdateNotificationTemplateRequest('census.published', 'T', 'B'));
    }

    public function test_calls_update_on_template_with_request_title_and_body(): void
    {
        $template = new NotificationTemplate('census.published', 'Old', 'Old');
        $this->repository->method('findByEvent')->willReturn($template);

        $this->handler->handle(new UpdateNotificationTemplateRequest('census.published', 'New Title', 'New Body'));

        self::assertSame('New Title', $template->title());
        self::assertSame('New Body', $template->body());
    }

    public function test_saves_template_via_repository(): void
    {
        $template = new NotificationTemplate('census.published', 'T', 'B');
        $this->repository->method('findByEvent')->willReturn($template);

        $this->repository->expects(self::once())->method('save')->with($template);

        $this->handler->handle(new UpdateNotificationTemplateRequest('census.published', 'T', 'B'));
    }

    public function test_transaction_order_is_begin_save_commit(): void
    {
        $template = new NotificationTemplate('census.published', 'T', 'B');
        $this->repository->method('findByEvent')->willReturn($template);

        $order = [];
        $this->transactionManager->method('begin')->willReturnCallback(function () use (&$order) { $order[] = 'begin'; });
        $this->repository->method('save')->willReturnCallback(function () use (&$order) { $order[] = 'save'; });
        $this->transactionManager->method('commit')->willReturnCallback(function () use (&$order) { $order[] = 'commit'; });

        $this->handler->handle(new UpdateNotificationTemplateRequest('census.published', 'T', 'B'));

        self::assertSame(['begin', 'save', 'commit'], $order);
    }

    public function test_rolls_back_and_rethrows_on_repository_save_failure(): void
    {
        $template = new NotificationTemplate('census.published', 'T', 'B');
        $this->repository->method('findByEvent')->willReturn($template);

        $exception = new \RuntimeException('DB error');
        $this->repository->method('save')->willThrowException($exception);

        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $this->handler->handle(new UpdateNotificationTemplateRequest('census.published', 'T', 'B'));
    }
}
