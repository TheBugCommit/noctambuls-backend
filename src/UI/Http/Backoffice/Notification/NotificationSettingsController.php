<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Notification;

use App\Notification\Application\GetNotificationTemplates\GetNotificationTemplatesHandler;
use App\Notification\Application\UpdateNotificationTemplate\UpdateNotificationTemplateHandler;
use App\Notification\Application\UpdateNotificationTemplate\UpdateNotificationTemplateRequest;
use App\Notification\Domain\Exception\NotificationTemplateNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotificationSettingsController extends AbstractController
{
    public function __construct(
        private readonly GetNotificationTemplatesHandler    $getHandler,
        private readonly UpdateNotificationTemplateHandler  $updateHandler,
    ) {
    }

    public function __invoke(Request $request, string $event = ''): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleUpdate($request, $event);
        }

        $templates = $this->getHandler->handle();

        return $this->render('backoffice/notification/settings.html.twig', [
            'templates' => $templates->items,
        ]);
    }

    private function handleUpdate(Request $request, string $event): Response
    {
        if (!$this->isCsrfTokenValid('notification_' . $event, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'notification.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_notifications');
        }

        try {
            $this->updateHandler->handle(new UpdateNotificationTemplateRequest(
                event: $event,
                title: trim((string) $request->request->get('title', '')),
                body: trim((string) $request->request->get('body', '')),
            ));
            $this->addFlash('success', 'notification.flash.updated');
        } catch (NotificationTemplateNotFoundException) {
            $this->addFlash('error', 'notification.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'notification.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_notifications');
    }
}
