<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\ListCensuses\ListCensusesHandler;
use App\Census\Application\ListCensuses\ListCensusesRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusListController extends AbstractController
{
    private const PAGE_SIZE = 20;

    public function __construct(
        private readonly ListCensusesHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = self::PAGE_SIZE;

        $response = $this->handler->handle(new ListCensusesRequest(page: $page, limit: $limit));

        $totalPages = $response->total > 0 ? (int) ceil($response->total / $limit) : 1;

        return $this->render('backoffice/census/list.html.twig', [
            'censuses'   => $response->censuses,
            'total'      => $response->total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
