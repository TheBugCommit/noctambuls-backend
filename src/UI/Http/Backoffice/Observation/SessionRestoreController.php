<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\RestoreSession\RestoreSessionHandler;
use App\Observation\Application\RestoreSession\RestoreSessionRequest;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionRestoreController extends AbstractController
{
    public function __construct(
        private readonly RestoreSessionHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('session_restore_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'session.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_session_trash');
        }

        try {
            $this->handler->handle(new RestoreSessionRequest($id));
            $this->addFlash('success', 'session.flash.restored');
        } catch (SessionNotFoundException $e) {
            $this->addFlash('error', 'session.flash.not_found');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Throwable) {
            $this->addFlash('error', 'session.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_session_trash');
    }
}
