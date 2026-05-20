<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\GetNotificationTemplates;

use App\Notification\Application\GetNotificationTemplates\GetNotificationTemplatesHandler;
use App\Notification\Domain\Entity\NotificationTemplate;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetNotificationTemplatesHandlerTest extends TestCase
{
    private NotificationTemplateRepositoryInterface&MockObject $repository;
    private GetNotificationTemplatesHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(NotificationTemplateRepositoryInterface::class);
        $this->handler    = new GetNotificationTemplatesHandler($this->repository);
    }

    public function test_returns_empty_items_when_repository_is_empty(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $response = $this->handler->handle();

        self::assertEmpty($response->items);
    }

    public function test_maps_template_to_array_with_event_title_body_keys(): void
    {
        $template = new NotificationTemplate('census.published', 'Title', 'Body text');
        $this->repository->method('findAll')->willReturn([$template]);

        $response = $this->handler->handle();

        self::assertCount(1, $response->items);
        self::assertSame('census.published', $response->items[0]['event']);
        self::assertSame('Title', $response->items[0]['title']);
        self::assertSame('Body text', $response->items[0]['body']);
    }

    public function test_maps_multiple_templates(): void
    {
        $templates = [
            new NotificationTemplate('census.published',    'Pub Title',  'Pub Body'),
            new NotificationTemplate('census.date_changed', 'Date Title', 'Date Body'),
            new NotificationTemplate('census.cancelled',    'Can Title',  'Can Body'),
        ];
        $this->repository->method('findAll')->willReturn($templates);

        $response = $this->handler->handle();

        self::assertCount(3, $response->items);
        self::assertSame('census.date_changed', $response->items[1]['event']);
    }

    public function test_calls_find_all_exactly_once(): void
    {
        $this->repository->expects(self::once())->method('findAll')->willReturn([]);

        $this->handler->handle();
    }
}
