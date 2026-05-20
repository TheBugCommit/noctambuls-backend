<?php

declare(strict_types=1);

namespace App\Tests\Notification\Infrastructure\EventListener;

use App\Census\Domain\Event\CensusCancelledEvent;
use App\Census\Domain\Event\CensusDateChangedEvent;
use App\Census\Domain\Event\CensusPublishedEvent;
use App\Notification\Domain\Entity\NotificationTemplate;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use App\Notification\Infrastructure\EventListener\CensusNotificationListener;
use App\Shared\Domain\Service\PushNotificationSenderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CensusNotificationListenerTest extends TestCase
{
    private NotificationTemplateRepositoryInterface&MockObject $repository;
    private PushNotificationSenderInterface&MockObject $sender;
    private LoggerInterface&MockObject $logger;
    private CensusNotificationListener $listener;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(NotificationTemplateRepositoryInterface::class);
        $this->sender     = $this->createMock(PushNotificationSenderInterface::class);
        $this->logger     = $this->createMock(LoggerInterface::class);
        $this->listener   = new CensusNotificationListener($this->repository, $this->sender, $this->logger);
    }

    public function test_on_census_published_sends_notification_with_template_data(): void
    {
        $template = new NotificationTemplate('census.published', 'Pub Title', 'Pub Body');
        $this->repository->method('findByEvent')->with('census.published')->willReturn($template);

        $this->sender->expects(self::once())->method('send')->with('Pub Title', 'Pub Body');

        $this->listener->onCensusPublished(new CensusPublishedEvent('census-id-1'));
    }

    public function test_on_census_date_changed_sends_notification(): void
    {
        $template = new NotificationTemplate('census.date_changed', 'Date Title', 'Date Body');
        $this->repository->method('findByEvent')->with('census.date_changed')->willReturn($template);

        $this->sender->expects(self::once())->method('send')->with('Date Title', 'Date Body');

        $this->listener->onCensusDateChanged(new CensusDateChangedEvent('census-id-2'));
    }

    public function test_on_census_cancelled_sends_notification(): void
    {
        $template = new NotificationTemplate('census.cancelled', 'Can Title', 'Can Body');
        $this->repository->method('findByEvent')->with('census.cancelled')->willReturn($template);

        $this->sender->expects(self::once())->method('send')->with('Can Title', 'Can Body');

        $this->listener->onCensusCancelled(new CensusCancelledEvent('census-id-3'));
    }

    public function test_logs_warning_and_does_not_send_when_template_not_found(): void
    {
        $this->repository->method('findByEvent')->willReturn(null);

        $this->sender->expects(self::never())->method('send');
        $this->logger->expects(self::once())->method('warning')->with(
            'FCM notification template not found',
            self::arrayHasKey('event')
        );

        $this->listener->onCensusPublished(new CensusPublishedEvent('census-id-4'));
    }

    public function test_logs_error_and_does_not_propagate_on_sender_exception(): void
    {
        $template = new NotificationTemplate('census.published', 'T', 'B');
        $this->repository->method('findByEvent')->willReturn($template);
        $this->sender->method('send')->willThrowException(new \RuntimeException('FCM down'));

        $this->logger->expects(self::once())->method('error')->with(
            'Failed to send FCM notification',
            self::arrayHasKey('error')
        );

        $this->listener->onCensusPublished(new CensusPublishedEvent('census-id-5'));
    }

    public function test_get_subscribed_events_returns_three_event_types(): void
    {
        $events = CensusNotificationListener::getSubscribedEvents();

        self::assertArrayHasKey(CensusPublishedEvent::class, $events);
        self::assertArrayHasKey(CensusDateChangedEvent::class, $events);
        self::assertArrayHasKey(CensusCancelledEvent::class, $events);
        self::assertCount(3, $events);
    }
}
