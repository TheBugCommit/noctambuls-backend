<?php

declare(strict_types=1);

namespace App\Notification\Application\GetNotificationTemplates;

use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;

final readonly class GetNotificationTemplatesHandler
{
    public function __construct(
        private NotificationTemplateRepositoryInterface $repository,
    ) {
    }

    public function handle(): GetNotificationTemplatesResponse
    {
        $templates = $this->repository->findAll();

        $items = array_map(
            static fn($t) => ['event' => $t->event(), 'title' => $t->title(), 'body' => $t->body()],
            $templates,
        );

        return new GetNotificationTemplatesResponse($items);
    }
}
