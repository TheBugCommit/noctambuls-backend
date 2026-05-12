<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Observation;

use App\Observation\Application\ExportSessions\ExportSessionsHandler;
use App\Observation\Application\ExportSessions\ExportSessionsRequest;
use App\Observation\Application\ExportSessions\SessionFileExporterInterface;
use App\UI\Http\Backoffice\Shared\DateRangeParserTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class SessionExportController extends AbstractController
{
    public function __construct(
        private readonly ExportSessionsHandler $handler,
        private readonly SessionFileExporterInterface $csvExporter,
        private readonly SessionFileExporterInterface $xlsxExporter,
    ) {
    }
    use DateRangeParserTrait;

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $format = $request->query->get('format', 'csv');
        if (!in_array($format, ['csv', 'xlsx'], true)) {
            $format = 'csv';
        }

        $sessionId      = $request->query->get('sessionId') ?: null;
        $sessionTypeRaw = $request->query->get('sessionType');
        $sessionType    = ($sessionTypeRaw === '' || $sessionTypeRaw === null)
            ? null
            : (int)$sessionTypeRaw;
        $censusId       = $request->query->get('censusId') ?: null;

        $hasObservationsRaw = $request->query->get('hasObservations');
        $hasObservations    = ($hasObservationsRaw !== null && $hasObservationsRaw !== '')
            ? (bool)(int)$hasObservationsRaw
            : null;

        [$dateFrom, $dateTo] = $this->parseDateRange($request);

        try {
            $exportResponse = $this->handler->handle(new ExportSessionsRequest(
                format:          $format,
                sessionId:       $sessionId,
                sessionType:     $sessionType,
                dateFrom:        $dateFrom,
                dateTo:          $dateTo,
                hasObservations: $hasObservations,
                censusId:        $censusId,
            ));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'session.error.exporting');
            return $this->redirectToRoute('backoffice_session_list');
        }

        $exporter = $format === 'xlsx' ? $this->xlsxExporter : $this->csvExporter;
        $filePath = $exporter->export($exportResponse);

        $mimeType = $format === 'xlsx'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/csv; charset=UTF-8';

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exportResponse->filename . '.' . $format,
        );
        $response->deleteFileAfterSend(true);
        $response->headers->set('Content-Type', $mimeType);

        return $response;
    }
}
