<?php

declare(strict_types=1);

namespace App\Notification\Domain\ValueObject;

enum NotificationEvent: string
{
    case PUBLISHED    = 'census.published';
    case DATE_CHANGED = 'census.date_changed';
    case CANCELLED    = 'census.cancelled';
}
