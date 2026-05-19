<?php

namespace App\UI\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DashExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('dash_if_empty', [$this, 'dashIfEmpty']),
        ];
    }

    public function dashIfEmpty($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '—';
        }

        return $value;
    }
}
