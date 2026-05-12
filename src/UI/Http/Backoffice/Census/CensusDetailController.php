<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\GetCensus\GetCensusHandler;
use App\Census\Application\GetCensus\GetCensusRequest;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Service\CensusLinkGeneratorInterface;
use App\Observation\Application\GetSessionsForCensus\GetSessionsForCensusHandler;
use App\Observation\Application\GetSessionsForCensus\GetSessionsForCensusRequest;
use App\Shared\Application\Service\QrCodeGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusDetailController extends AbstractController
{
    public function __construct(
        private readonly GetCensusHandler $getCensusHandler,
        private readonly GetSessionsForCensusHandler $getSessionsHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        try {
            $census = $this->getCensusHandler->handle(new GetCensusRequest($id));
        } catch (CensusNotFoundException) {
            $this->addFlash('error', 'census.error.not_found');
            return $this->redirectToRoute('backoffice_census_list');
        }

        $sessionsResponse = $this->getSessionsHandler->handle(new GetSessionsForCensusRequest($id));

        $mapMarkers = array_values(array_filter(array_map(
            fn(array $s) => $s['latitude'] !== null ? [
                'lat'   => $s['latitude'],
                'lng'   => $s['longitude'],
                'label' => 'LP ' . ($s['listeningPoint'] ?? '–'),
            ] : null,
            $sessionsResponse->sessions,
        )));

        return $this->render('backoffice/census/detail.html.twig', [
            'census'     => $census,
            'sessions'   => $sessionsResponse->sessions,
            'mapMarkers' => $mapMarkers,
        ]);
    }
}
