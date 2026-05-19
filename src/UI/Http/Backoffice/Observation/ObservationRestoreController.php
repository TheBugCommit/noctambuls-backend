<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\RestoreObservation\RestoreObservationHandler;
use App\Observation\Application\RestoreObservation\RestoreObservationRequest;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ObservationRestoreController extends AbstractController
{
    public function __construct(
        private readonly RestoreObservationHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('observation_restore_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'observation.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_observation_detail', ['id' => $id]);
        }

        try {
            $this->handler->handle(new RestoreObservationRequest($id));
            $this->addFlash('success', 'observation.flash.restored');
        } catch (SessionNotFoundException|ObservationNotFoundException) {
            $this->addFlash('error', 'observation.flash.not_found');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Throwable) {
            $this->addFlash('error', 'observation.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_observation_detail', ['id' => $id]);
    }
}
