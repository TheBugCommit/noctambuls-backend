<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface PushNotificationSenderInterface
{
    public function send(string $title, string $body): void;
}
