<?php

declare(strict_types=1);

namespace App\Analytics\Domain\ValueObject;

enum CronJobName: string
{
    case UpdateAnalytics = 'update_analytics';
}
