<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\BirdCatalog;

use App\BirdCatalog\Application\DeleteBird\DeleteBirdHandler;
use App\BirdCatalog\Application\DeleteBird\DeleteBirdRequest;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BirdDeleteController extends AbstractController
{
    public function __construct(
        private readonly DeleteBirdHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('bird_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('backoffice_bird_catalog_list');
        }

        try {
            $this->handler->handle(new DeleteBirdRequest($id));
            $this->addFlash('success', 'bird_catalog.flash.deleted');
        } catch (BirdNotFoundException) {
            $this->addFlash('error', 'bird_catalog.flash.not_found');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'bird_catalog.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_bird_catalog_list');
    }
}
