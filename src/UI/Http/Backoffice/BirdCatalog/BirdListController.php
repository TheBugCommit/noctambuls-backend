<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\BirdCatalog;

use App\BirdCatalog\Application\ListBirds\ListBirdsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class BirdListController extends AbstractController
{
    public function __construct(
        private readonly ListBirdsHandler $handler,
    ) {
    }

    public function __invoke(): Response
    {
        $response = $this->handler->handle();

        return $this->render('backoffice/bird_catalog/list.html.twig', [
            'birds' => $response->birds,
        ]);
    }
}
