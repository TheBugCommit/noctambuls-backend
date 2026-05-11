<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\ListCensuses\ListCensusesHandler;
use App\Census\Application\ListCensuses\ListCensusesRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class CensusListController extends AbstractController
{
    public function __construct(
        private readonly ListCensusesHandler $handler,
    ) {
    }

    public function __invoke(): Response
    {
        $response = $this->handler->handle(new ListCensusesRequest(page: 1, limit: 100));

        return $this->render('backoffice/census/list.html.twig', [
            'censuses' => $response->censuses,
            'total'    => $response->total,
        ]);
    }
}
