<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Census;

use App\Census\Application\DeleteCensus\DeleteCensusHandler;
use App\Census\Application\DeleteCensus\DeleteCensusRequest;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Exception\CensusNotEditableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CensusDeleteController extends AbstractController
{
    public function __construct(
        private readonly DeleteCensusHandler $handler,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('census_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'census.error.invalid_csrf');
            return $this->redirectToRoute('backoffice_census_list');
        }

        try {
            $this->handler->handle(new DeleteCensusRequest($id));
            $this->addFlash('success', 'census.flash.deleted');
        } catch (CensusNotFoundException) {
            $this->addFlash('error', 'census.error.not_found');
        } catch (CensusNotEditableException) {
            $this->addFlash('error', 'census.error.not_editable');
        } catch (\Throwable) {
            $this->addFlash('error', 'census.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_census_list');
    }
}
