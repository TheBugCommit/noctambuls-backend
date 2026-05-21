<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\GetSessionsForBackoffice\GetSessionsForBackofficeHandler;
use App\Observation\Application\GetSessionsForBackoffice\GetSessionsForBackofficeRequest;
use App\UI\Http\Backoffice\Shared\DateRangeParserTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionListController extends AbstractController
{
    public function __construct(
        private readonly GetSessionsForBackofficeHandler $handler,
    ) {
    }

    use DateRangeParserTrait;

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 20;

        $sessionId = $request->query->get('sessionId') ?: null;
        $sessionTypeRaw = $request->query->get('sessionType');
        $sessionType = $sessionTypeRaw === '' || $sessionTypeRaw === null
            ? null
            : (int) $sessionTypeRaw;
        $censusId = $request->query->get('censusId') ?: null;
        $hasObservationsRaw = $request->query->get('hasObservations');
        $hasObservations = $hasObservationsRaw !== null && $hasObservationsRaw !== '' ? (bool)(int)$hasObservationsRaw : null;
        $ornithologistCode = $request->query->get('ornithologistCode') ?: null;
        $listeningPointRaw = $request->query->get('listeningPoint');
        $listeningPoint = ($listeningPointRaw !== null && $listeningPointRaw !== '') ? (int)$listeningPointRaw : null;
        $censusTitle = $request->query->get('censusTitle') ?: null;

        [$dateFrom, $dateTo] = $this->parseDateRange($request);

        try {
            $response = $this->handler->handle(new GetSessionsForBackofficeRequest(
                page: $page,
                limit: $limit,
                sessionId: $sessionId,
                sessionType: $sessionType,
                dateFrom: $dateFrom,
                dateTo: $dateTo,
                hasObservations: $hasObservations,
                censusId: $censusId,
                ornithologistCode: $ornithologistCode,
                listeningPoint: $listeningPoint,
                censusTitle: $censusTitle,
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('backoffice_session_list');
        }

        return $this->render('backoffice/sessions/list.html.twig', [
            'sessions' => $response->sessions,
            'total' => $response->total,
            'page' => $response->page,
            'limit' => $response->limit,
            'totalPages' => $response->totalPages(),
            'filters' => [
                'sessionId' => $sessionId,
                'sessionType' => $sessionType,
                'dateFrom' => $request->query->get('dateFrom') ?: '',
                'dateTo' => $request->query->get('dateTo') ?: '',
                'hasObservations' => $hasObservationsRaw ?? '',
                'censusId' => $censusId,
                'ornithologistCode' => $ornithologistCode,
                'listeningPoint' => $listeningPointRaw ?? '',
                'censusTitle' => $censusTitle,
            ],
        ]);
    }
}
