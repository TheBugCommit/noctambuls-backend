<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\SoftDeleteObservation\SoftDeleteObservationHandler;
use App\Observation\Application\SoftDeleteObservation\SoftDeleteObservationRequest;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ObservationDeleteController extends AbstractController
{
    public function __construct(
        private readonly SoftDeleteObservationHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('observation_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'observation.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_observation_detail', ['id' => $id]);
        }

        try {
            $this->handler->handle(new SoftDeleteObservationRequest($id));
            $this->addFlash('success', 'observation.flash.deleted');
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
