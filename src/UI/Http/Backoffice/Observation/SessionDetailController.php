<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\GetSessionDetail\GetSessionDetailHandler;
use App\Observation\Application\GetSessionDetail\GetSessionDetailRequest;
use App\Observation\Application\UpdateSession\UpdateSessionHandler;
use App\Observation\Application\UpdateSession\UpdateSessionRequest;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionDetailController extends AbstractController
{
    public function __construct(
        private readonly GetSessionDetailHandler $getHandler,
        private readonly UpdateSessionHandler $updateHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($request->isMethod('POST')) {

            if (!$this->isCsrfTokenValid('session_edit', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'session.error.invalid_csrf');
                return $this->redirectToRoute('backoffice_session_detail', ['id' => $id]);
            }

            try {
                $lpRaw = $request->request->get('listeningPoint');
                $this->updateHandler->handle(new UpdateSessionRequest(
                    sessionId: $id,
                    temperature: (int)$request->request->get('temperature'),
                    wind: (int)$request->request->get('wind'),
                    sky: (int)$request->request->get('sky'),
                    rain: (int)$request->request->get('rain'),
                    visibility: (int)$request->request->get('visibility'),
                    moon: (int)$request->request->get('moon'),
                    listeningPoint: $lpRaw !== null && $lpRaw !== '' ? (int)$lpRaw : null,
                ));
                $this->addFlash('success', 'session.flash.updated');
            } catch (SessionNotFoundException $e) {
                $this->addFlash('error', 'session.flash.not_found');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable) {
                $this->addFlash('error', 'session.error.unexpected');
            }

            return $this->redirectToRoute('backoffice_session_detail', ['id' => $id]);
        }

        try {
            $response = $this->getHandler->handle(new GetSessionDetailRequest($id));
        } catch (SessionNotFoundException $e) {
            $this->addFlash('error', 'session.flash.not_found');
            return $this->redirectToRoute('backoffice_session_list');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('backoffice_session_list');
        } catch (\Throwable) {
            $this->addFlash('error', 'session.error.unexpected');
            return $this->redirectToRoute('backoffice_session_list');
        }

        return $this->render('backoffice/sessions/detail.html.twig', [
            'session' => $response->session,
        ]);
    }
}
