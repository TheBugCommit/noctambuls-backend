<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\BirdCatalog;

use App\BirdCatalog\Application\GetBird\GetBirdHandler;
use App\BirdCatalog\Application\GetBird\GetBirdRequest;
use App\BirdCatalog\Application\UpdateBird\UpdateBirdHandler;
use App\BirdCatalog\Application\UpdateBird\UpdateBirdRequest;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\UI\Http\Backoffice\BirdCatalog\Mapper\BirdFormMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BirdEditController extends AbstractController
{
    public function __construct(
        private readonly GetBirdHandler    $getBirdHandler,
        private readonly UpdateBirdHandler $updateBirdHandler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        try {
            $bird = $this->getBirdHandler->handle(new GetBirdRequest($id));
        } catch (BirdNotFoundException) {
            $this->addFlash('error', 'bird_catalog.flash.not_found');
            return $this->redirectToRoute('backoffice_bird_catalog_list');
        }

        if ($request->isMethod('GET')) {
            return $this->render('backoffice/bird_catalog/edit.html.twig', [
                'bird' => $bird,
            ]);
        }

        if (!$this->isCsrfTokenValid('bird_catalog', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'bird_catalog.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_bird_catalog_edit', ['id' => $id]);
        }

        try {
            $this->updateBirdHandler->handle(new UpdateBirdRequest(
                $id,
                trim((string) $request->request->get('scientificName', '')),
                trim((string) $request->request->get('informationUrl', '')),
                BirdFormMapper::mapTranslations($request->request->all('translations')),
                BirdFormMapper::mapFile($request->files->get('imageFile')),
                BirdFormMapper::mapFile($request->files->get('audioFile'))
            ));

            $this->addFlash('success', 'bird_catalog.flash.updated');
            return $this->redirectToRoute('backoffice_bird_catalog_list');

        } catch (BirdNotFoundException) {
            $this->addFlash('error', 'bird_catalog.flash.not_found');
            return $this->redirectToRoute('backoffice_bird_catalog_list');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->render('backoffice/bird_catalog/edit.html.twig', [
                'bird' => $bird,
                'data' => $request->request->all()
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'bird_catalog.error.unexpected');
            return $this->render('backoffice/bird_catalog/edit.html.twig', [
                'bird' => $bird,
                'data' => $request->request->all()
            ]);
        }
    }
}
