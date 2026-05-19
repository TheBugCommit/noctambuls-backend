<?php

namespace App\UI\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_time', [$this, 'formatTime']),
            new TwigFilter('format_datetime', [$this, 'formatDatetime']),
        ];
    }

    public function formatDatetime($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '—';
        }

        try {
            $date = $value instanceof \DateTimeInterface ? $value : new \DateTime($value);

            $date->setTimezone(new \DateTimeZone('Europe/Madrid'));

            return $date->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function formatTime($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '—';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        try {
            $date = new \DateTime($value);
            return $date->format('H:i');
        } catch (\Exception $e) {
            return $value;
        }
    }
}
