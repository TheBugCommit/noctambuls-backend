<?php

declare(strict_types=1);

namespace App\Notification\Application\UpdateNotificationTemplate;

use App\Notification\Domain\Exception\NotificationTemplateNotFoundException;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class UpdateNotificationTemplateHandler
{
    public function __construct(
        private NotificationTemplateRepositoryInterface $repository,
        private TransactionManagerInterface             $transactionManager,
    ) {
    }

    /** @throws Throwable */
    public function handle(UpdateNotificationTemplateRequest $request): void
    {
        $template = $this->repository->findByEvent($request->event);

        if ($template === null) {
            throw new NotificationTemplateNotFoundException();
        }

        $template->update($request->title, $request->body);

        $this->transactionManager->begin();
        try {
            $this->repository->save($template);
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
