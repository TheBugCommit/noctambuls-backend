<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\BirdCatalog;

use App\BirdCatalog\Application\CreateBird\CreateBirdHandler;
use App\BirdCatalog\Application\CreateBird\CreateBirdRequest;
use App\UI\Http\Backoffice\BirdCatalog\Mapper\BirdFormMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BirdCreateController extends AbstractController
{
    public function __construct(
        private readonly CreateBirdHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('backoffice/bird_catalog/create.html.twig');
        }

        if (!$this->isCsrfTokenValid('bird_catalog', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'bird_catalog.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_bird_catalog_create');
        }

        $imageDto = BirdFormMapper::mapFile($request->files->get('imageFile'));
        $audioDto = BirdFormMapper::mapFile($request->files->get('audioFile'));

        if ($imageDto === null || $audioDto === null) {
            $this->addFlash('error', 'bird_catalog.error.missing_files');
            return $this->render('backoffice/bird_catalog/create.html.twig', ['data' => $request->request->all()]);
        }

        try {
            $this->handler->handle(new CreateBirdRequest(
                trim((string) $request->request->get('scientificName', '')),
                $imageDto,
                $audioDto,
                trim((string) $request->request->get('informationUrl', '')),
                BirdFormMapper::mapTranslations($request->request->all('translations')),
            ));

            $this->addFlash('success', 'bird_catalog.flash.created');
            return $this->redirectToRoute('backoffice_bird_catalog_list');

        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->render('backoffice/bird_catalog/create.html.twig', [
                'data' => $request->request->all(),
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'bird_catalog.error.unexpected');
            return $this->render('backoffice/bird_catalog/create.html.twig', [
                'data' => $request->request->all(),
            ]);
        }
    }
}
