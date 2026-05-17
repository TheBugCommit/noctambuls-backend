<?php

declare(strict_types=1);

namespace App\Notification\Application\UpdateNotificationTemplate;

readonly class UpdateNotificationTemplateRequest
{
    public function __construct(
        public string $event,
        public string $title,
        public string $body,
    ) {
    }
}
