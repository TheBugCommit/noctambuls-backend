<?php

declare(strict_types=1);

namespace App\Notification\Application\GetNotificationTemplates;

readonly class GetNotificationTemplatesResponse
{
    public function __construct(public array $items) {}
}
