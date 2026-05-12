<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\Shared;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

trait DateRangeParserTrait
{
    /**
     * @return array{0: ?DateTimeImmutable, 1: ?DateTimeImmutable}
     */
    private function parseDateRange(Request $request): array
    {
        $dateFrom = null;
        $dateTo = null;

        try {
            if ($request->query->get('dateFrom')) {
                $dateFrom = new DateTimeImmutable($request->query->get('dateFrom'));
            }
            if ($request->query->get('dateTo')) {
                $dateTo = new DateTimeImmutable($request->query->get('dateTo'))->setTime(23, 59, 59);
            }
        } catch (\Exception) {
            // Silenciem l'error i retornem nulls si el format és invàlid
        }

        return [$dateFrom, $dateTo];
    }
}
