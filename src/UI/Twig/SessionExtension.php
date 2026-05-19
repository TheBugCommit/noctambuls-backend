<?php

namespace App\UI\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SessionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('session_type_config', [$this, 'getSessionTypeConfig']),
            new TwigFilter('contact_type_config', [$this, 'getContactTypeConfig']),
            new TwigFilter('location_source_config', [$this, 'getLocationSourceConfig']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('env_options', [$this, 'getEnvOptions']),
        ];
    }

    public function getContactTypeConfig($value): array
    {
        $mapping = [
            0 => ['key' => 'visual', 'icon' => 'lucide:eye'],
            1 => ['key' => 'audio',  'icon' => 'lucide:ear'],
        ];

        return $mapping[$value] ?? ['key' => 'unknown', 'icon' => 'lucide:help-circle'];
    }
    public function getSessionTypeConfig($value): array
    {
        $mapping = [
            0 => [
                'key' => 'incidental',
                'color' => 'badge-accent',
                'icon' => 'lucide:eye'
            ],
            2 => [
                'key' => 'station',
                'color' => 'badge-secondary',
                'icon' => 'lucide:map-pin'
            ],
            1 => [
                'key' => 'census',
                'color' => 'badge-primary',
                'icon' => 'lucide:users'
            ],
        ];

        return $mapping[$value] ?? [
            'key' => 'unknown',
            'color' => 'badge-ghost',
            'icon' => 'lucide:tag'
        ];
    }

    public function getEnvOptions(string $type): array
    {
        $options = [
            'temperature' => [
                0 => 'session.env.temp.subzero',
                1 => 'session.env.temp.0_10',
                2 => 'session.env.temp.10_20',
                3 => 'session.env.temp.20_30',
                4 => 'session.env.temp.over30',
            ],
            'wind' => [
                0 => 'session.env.wind.calm',
                1 => 'session.env.wind.breeze',
                2 => 'session.env.wind.moderate',
            ],
            'sky' => [
                0 => 'session.env.sky.clear',
                1 => 'session.env.sky.partly_cloudy',
                2 => 'session.env.sky.overcast',
            ],
            'rain' => [
                0 => 'session.env.rain.none',
                1 => 'session.env.rain.drizzle',
                2 => 'session.env.rain.storm',
            ],
            'visibility' => [
                0 => 'session.env.visibility.bad',
                1 => 'session.env.visibility.moderate',
                2 => 'session.env.visibility.good',
            ],
            'moon' => [
                0 => 'session.env.moon.absent',
                1 => 'session.env.moon.present',
            ],
        ];

        return $options[$type] ?? [];
    }

    public function getLocationSourceConfig($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '—';
        }

        $mapping = [
            0          => 'session.location_source.gps',
            1          => 'session.location_source.manual',
            'gps'      => 'session.location_source.gps',
            'manual'   => 'session.location_source.manual',
        ];

        return $mapping[$value] ?? (string) $value;
    }
}

