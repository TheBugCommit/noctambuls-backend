<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\EventListener;

use App\Census\Domain\Event\CensusCancelledEvent;
use App\Census\Domain\Event\CensusDateChangedEvent;
use App\Census\Domain\Event\CensusPublishedEvent;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use App\Shared\Domain\Service\PushNotificationSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CensusNotificationListener implements EventSubscriberInterface
{
    public function __construct(
        private NotificationTemplateRepositoryInterface $templateRepository,
        private PushNotificationSenderInterface         $sender,
        private LoggerInterface                         $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CensusPublishedEvent::class    => 'onCensusPublished',
            CensusDateChangedEvent::class  => 'onCensusDateChanged',
            CensusCancelledEvent::class    => 'onCensusCancelled',
        ];
    }

    public function onCensusPublished(CensusPublishedEvent $event): void
    {
        $this->notifyWith('census.published');
    }

    public function onCensusDateChanged(CensusDateChangedEvent $event): void
    {
        $this->notifyWith('census.date_changed');
    }

    public function onCensusCancelled(CensusCancelledEvent $event): void
    {
        $this->notifyWith('census.cancelled');
    }

    private function notifyWith(string $eventKey): void
    {
        try {
            $template = $this->templateRepository->findByEvent($eventKey);

            if ($template === null) {
                $this->logger->warning('FCM notification template not found', ['event' => $eventKey]);
                return;
            }

            $this->sender->send($template->title(), $template->body());
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send FCM notification', [
                'event'   => $eventKey,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
