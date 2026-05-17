<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Entity\NotificationTemplate;

interface NotificationTemplateRepositoryInterface
{
    public function findByEvent(string $event): ?NotificationTemplate;

    /** @return NotificationTemplate[] */
    public function findAll(): array;

    public function save(NotificationTemplate $template): void;
}
