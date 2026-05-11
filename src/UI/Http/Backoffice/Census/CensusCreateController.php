<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\CreateCensus\CreateCensusHandler;
use App\Census\Application\CreateCensus\CreateCensusRequest;
use App\Shared\Domain\Exception\EmptyCoordinateException;
use App\Shared\Domain\Exception\InvalidCoordinateException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusCreateController extends AbstractController
{
    public function __construct(
        private readonly CreateCensusHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('backoffice/census/create.html.twig');
        }

        if (!$this->isCsrfTokenValid('census', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'census.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_census_create');
        }

        try {
            $this->handler->handle(new CreateCensusRequest(
                title: trim((string) $request->request->get('title', '')),
                date: trim((string) $request->request->get('date', '')),
                time: trim((string) $request->request->get('time', '')),
                latitude: $request->request->get('latitude'),
                longitude: $request->request->get('longitude'),
                locationName: trim((string) $request->request->get('locationName', '')),
                description: trim((string) $request->request->get('description', '')) ?: null,
            ));

            $this->addFlash('success', 'census.flash.created');
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
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->render('backoffice/census/create.html.twig', ['data' => $request->request->all()]);
        } catch (\Throwable) {
            $this->addFlash('error', 'census.error.unexpected');
            return $this->render('backoffice/census/create.html.twig', ['data' => $request->request->all()]);
        }
    }
}
