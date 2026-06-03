<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Event;

use App\Observation\Domain\Event\BeforeSessionsPurgedEvent;
use App\Observation\Domain\Service\SessionEventPublisherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class SymfonySessionEventPublisher implements SessionEventPublisherInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher) {}

    public function publishBeforeSessionsPurged(?\DateTimeImmutable $cutoff = null): void
    {
        $this->eventDispatcher->dispatch(new BeforeSessionsPurgedEvent($cutoff));
    }
}
