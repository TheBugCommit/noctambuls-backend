<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\PurgeDeletedSessions\PurgeDeletedSessionsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionTrashPurgeController extends AbstractController
{
    public function __construct(
        private readonly PurgeDeletedSessionsHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('session_purge', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'session.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_session_trash');
        }

        try {
            $this->handler->handle();
            $this->addFlash('success', 'session.flash.purged');
        } catch (\Throwable) {
            $this->addFlash('error', 'session.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_session_trash');
    }
}
