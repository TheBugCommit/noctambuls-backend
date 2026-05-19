<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\GetObservationDetail\GetObservationDetailHandler;
use App\Observation\Application\GetObservationDetail\GetObservationDetailRequest;
use App\Observation\Application\UpdateObservation\UpdateObservationHandler;
use App\Observation\Application\UpdateObservation\UpdateObservationRequest;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ObservationDetailController extends AbstractController
{
    public function __construct(
        private readonly GetObservationDetailHandler $getHandler,
        private readonly UpdateObservationHandler $updateHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('observation_edit', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'observation.error.invalid_csrf');
                return $this->redirectToRoute('backoffice_observation_detail', ['id' => $id]);
            }

            try {
                $notes = $request->request->get('notes');
                $this->updateHandler->handle(new UpdateObservationRequest(
                    observationId: $id,
                    speciesId: (string)$request->request->get('speciesId'),
                    contactType: (int)$request->request->get('contactType'),
                    quantity: (int)$request->request->get('quantity'),
                    notes: $notes !== '' ? $notes : null,
                    bearing: (float)$request->request->get('bearing'),
                ));
                $this->addFlash('success', 'observation.flash.updated');
            } catch (SessionNotFoundException $e) {
                $this->addFlash('error', 'session.flash.not_found');
            } catch (ObservationNotFoundException $e) {
                $this->addFlash('error', 'observation.flash.not_found');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable) {
                $this->addFlash('error', 'observation.error.unexpected');
            }

            return $this->redirectToRoute('backoffice_observation_detail', ['id' => $id]);
        }

        try {
            $response = $this->getHandler->handle(new GetObservationDetailRequest($id));
        } catch (SessionNotFoundException $e) {
            $this->addFlash('error', 'session.flash.not_found');
            return $this->redirectToRoute('backoffice_session_list');
        } catch (ObservationNotFoundException $e) {
            $this->addFlash('error', 'observation.flash.not_found');
            return $this->redirectToRoute('backoffice_session_list');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('backoffice_session_list');
        } catch (\Throwable) {
            $this->addFlash('error', 'observation.error.unexpected');
            return $this->redirectToRoute('backoffice_session_list');
        }

        return $this->render('backoffice/observations/detail.html.twig', [
            'observation' => $response->observation,
            'birds' => $response->birds,
        ]);
    }
}
