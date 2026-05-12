<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\SoftDeleteSession\SoftDeleteSessionHandler;
use App\Observation\Application\SoftDeleteSession\SoftDeleteSessionRequest;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionDeleteController extends AbstractController
{
    public function __construct(
        private readonly SoftDeleteSessionHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('session_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'session.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_session_detail', ['id' => $id]);
        }

        try {
            $this->handler->handle(new SoftDeleteSessionRequest($id));
            $this->addFlash('success', 'session.flash.deleted');
        } catch (SessionNotFoundException $e) {
            $this->addFlash('error', 'session.flash.not_found');
            return $this->redirectToRoute('backoffice_session_list');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('backoffice_session_detail', ['id' => $id]);
        } catch (\Throwable) {
            $this->addFlash('error', 'session.error.unexpected');
            return $this->redirectToRoute('backoffice_session_detail', ['id' => $id]);
        }

        return $this->redirectToRoute('backoffice_session_trash');
    }
}
