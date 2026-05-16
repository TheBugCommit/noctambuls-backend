<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Event;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Service\AnalyticsEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class SymfonyAnalyticsEventDispatcher implements AnalyticsEventDispatcherInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher) {}

    public function dispatch(AnalyticsDataCollectedEvent $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }
}
