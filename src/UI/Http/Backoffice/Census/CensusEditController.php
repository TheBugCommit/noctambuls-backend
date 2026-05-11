<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\GetCensus\GetCensusHandler;
use App\Census\Application\GetCensus\GetCensusRequest;
use App\Census\Application\UpdateCensus\UpdateCensusHandler;
use App\Census\Application\UpdateCensus\UpdateCensusRequest;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Exception\CensusNotEditableException;
use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusEditController extends AbstractController
{
    public function __construct(
        private readonly GetCensusHandler    $getHandler,
        private readonly UpdateCensusHandler $updateHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        try {
            $census = $this->getHandler->handle(new GetCensusRequest($id));
        } catch (CensusNotFoundException) {
            $this->addFlash('error', 'census.error.not_found');
            return $this->redirectToRoute('backoffice_census_list');
        }

        if ($request->isMethod('GET')) {
            return $this->render('backoffice/census/edit.html.twig', ['census' => $census]);
        }

        if (!$this->isCsrfTokenValid('census', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'census.error.invalid_csrf');
            return $this->render('backoffice/census/edit.html.twig', ['census' => $census]);
        }

        try {
            $this->updateHandler->handle(new UpdateCensusRequest(
                id: $id,
                title: trim((string) $request->request->get('title', '')),
                date: trim((string) $request->request->get('date', '')),
                time: trim((string) $request->request->get('time', '')),
                latitude: $request->request->get('latitude'),
                longitude: $request->request->get('longitude'),
                locationName: trim((string) $request->request->get('locationName', '')),
                description: trim((string) $request->request->get('description', '')) ?: null,
            ));

            $this->addFlash('success', 'census.flash.updated');
            return $this->redirectToRoute('backoffice_census_list');
        } catch (EmptyCoordinateException $e) {
            $message = str_contains($e->getMessage(), 'Latitude')
                ? 'census.error.latitude_required'
                : 'census.error.longitude_required';

            $this->addFlash('error', $message);
            return $this->render('backoffice/census/create.html.twig', ['data' => $request->request->all()]);
        } catch (InvalidCoordinateException $e) {
            $this->addFlash('error', 'census.error.coordinate_invalid');
            return $this->render('backoffice/census/create.html.twig', ['data' => $request->request->all()]);
        } catch (CensusNotEditableException) {
            $this->addFlash('error', 'census.error.not_editable');
            return $this->render('backoffice/census/edit.html.twig', ['census' => $census]);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->render('backoffice/census/edit.html.twig', ['census' => $census]);
        } catch (\Throwable) {
            $this->addFlash('error', 'census.error.unexpected');
            return $this->render('backoffice/census/edit.html.twig', ['census' => $census]);
        }
    }
}
