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
                $dateFrom = new \DateTimeImmutable(
                    $request->query->get('dateFrom') . ' 00:00:00',
                    new \DateTimeZone('Europe/Madrid')
                )->setTimezone(new \DateTimeZone('UTC'));
            }
            if ($request->query->get('dateTo')) {
                $dateTo = new \DateTimeImmutable(
                    $request->query->get('dateTo') . ' 23:59:59',
                    new \DateTimeZone('Europe/Madrid')
                )->setTimezone(new \DateTimeZone('UTC'));
            }
        } catch (\Exception) {
            // Silenciem l'error i retornem nulls si el format és invàlid
        }

        return [$dateFrom, $dateTo];
    }
}
