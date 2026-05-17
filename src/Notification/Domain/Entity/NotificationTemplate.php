<?php

declare(strict_types=1);

namespace App\Notification\Domain\Entity;

class NotificationTemplate
{
    public function __construct(
        private string $event,
        private string $title,
        private string $body,
    ) {
    }

    public function update(string $title, string $body): void
    {
        $this->title = $title;
        $this->body  = $body;
    }

    public function event(): string
    {
        return $this->event;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): string
    {
        return $this->body;
    }
}
