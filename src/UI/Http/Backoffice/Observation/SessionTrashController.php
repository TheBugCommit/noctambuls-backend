<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\GetDeletedSessions\GetDeletedSessionsHandler;
use App\Observation\Application\GetDeletedSessions\GetDeletedSessionsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionTrashController extends AbstractController
{
    public function __construct(
        private readonly GetDeletedSessionsHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 20;

        $response = $this->handler->handle(new GetDeletedSessionsRequest(
            page: $page,
            limit: $limit,
        ));

        return $this->render('backoffice/sessions/trash.html.twig', [
            'sessions' => $response->sessions,
            'total' => $response->total,
            'page' => $response->page,
            'limit' => $response->limit,
            'totalPages' => $response->totalPages(),
        ]);
    }
}
