<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\PublishCensus\PublishCensusHandler;
use App\Census\Application\PublishCensus\PublishCensusRequest;
use App\Census\Domain\Exception\CensusNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusPublishController extends AbstractController
{
    public function __construct(
        private readonly PublishCensusHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('census_publish_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'census.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_census_list');
        }

        try {
            $this->handler->handle(new PublishCensusRequest($id));
            $this->addFlash('success', 'census.flash.published');
        } catch (CensusNotFoundException) {
            $this->addFlash('error', 'census.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'census.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_census_list');
    }
}
