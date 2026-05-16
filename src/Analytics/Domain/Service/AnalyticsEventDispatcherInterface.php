<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Service;

use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;

interface AnalyticsEventDispatcherInterface
{
    public function dispatch(AnalyticsDataCollectedEvent $event): void;
}
