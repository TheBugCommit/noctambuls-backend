<?php

declare(strict_types=1);

namespace App\Observation\Application\Mapper;

use App\Observation\Domain\Entity\Session;

final class SessionExportMapper
{
    public function __construct(private readonly CatalanLabelMapper $labels)
    {
    }

    /** @return string[] */
    public function headers(): array
    {
        return [
            'ID Sessió',
            'Anotador',
            'Cens',
            'Tipus de sessió',
            'Hora d\'inici',
            'Hora de fi',
            'Temperatura',
            'Vent',
            'Cel',
            'Pluja',
            'Visibilitat',
            'Lluna',
            'Punt d\'escolta',
            'Latitud sessió',
            'Longitud sessió',
            'Participants',
            'ID Observació',
            'Espècie',
            'Tipus de contacte',
            'Quantitat',
            'Inici observació',
            'Fi observació',
            'Notes',
            'Latitud observació',
            'Longitud observació',
            'Azimut',
        ];
    }

    /**
     * @param Session[] $sessions
     * @param array<string, string> $profileMap
     * @param array<string, string> $censusMap
     * @param array<string, string> $birdMap
     * @return array<int, array<string, string|null>>
     */
    public function toFlatRows(array $sessions, array $profileMap, array $censusMap, array $birdMap): array
    {
        $rows = [];

        foreach ($sessions as $session) {
            $participants = [];
            foreach ($session->participants() as $p) {
                $participants[] = $p->ornithologistCode()->value();
            }

            $sessionCols = [
                'session_id'      => $session->id()->value(),
                'annotator'       => $profileMap[$session->annotatorId()->value()] ?? $session->annotatorId()->value(),
                'census'          => $censusMap[$session->censusId()?->value()] ?? '',
                'session_type'    => $this->labels->sessionType($session->sessionType()),
                'start_time'      => $session->startTime()?->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('d/m/Y H:i') ?? '',
                'end_time'        => $session->endTime()?->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('d/m/Y H:i') ?? '',
                'temperature'     => $this->labels->temperatureRange($session->temperature()),
                'wind'            => $this->labels->windLevel($session->wind()),
                'sky'             => $this->labels->skyCondition($session->sky()),
                'rain'            => $this->labels->rainLevel($session->rain()),
                'visibility'      => $this->labels->visibilityLevel($session->visibility()),
                'moon'            => $this->labels->moonPresence($session->moon()),
                'listening_point' => (string)$session->listeningPoint()?->value(),
                'session_lat'     => (string)$session->latitude()?->value(),
                'session_lon'     => (string)$session->longitude()?->value(),
                'participants'    => implode(', ', $participants),
            ];

            $activeObs = [];
            foreach ($session->observations() as $obs) {
                if (!$obs->isDeleted()) {
                    $activeObs[] = $obs;
                }
            }

            if (empty($activeObs)) {
                $rows[] = array_merge($sessionCols, [
                    'obs_id'       => '',
                    'species'      => '',
                    'contact_type' => '',
                    'quantity'     => '',
                    'obs_start'    => '',
                    'obs_end'      => '',
                    'notes'        => '',
                    'obs_lat'      => '',
                    'obs_lon'      => '',
                    'obs_bearing'  => '',
                ]);
            } else {
                foreach ($activeObs as $obs) {
                    $rows[] = array_merge($sessionCols, [
                        'obs_id'       => $obs->id()->value(),
                        'species'      => $birdMap[$obs->speciesId()->value()] ?? $obs->speciesId()->value(),
                        'contact_type' => $this->labels->contactType($obs->contactType()),
                        'quantity'     => (string)$obs->quantity()->value(),
                        'obs_start'    => $obs->startTime()->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('d/m/Y H:i'),
                        'obs_end'      => $obs->endTime()->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('d/m/Y H:i'),
                        'notes'        => $obs->notes() ?? '',
                        'obs_lat'      => (string)$obs->latitude()?->value(),
                        'obs_lon'      => (string)$obs->longitude()?->value(),
                        'obs_bearing'  => (string)$obs->bearing()->value(),
                    ]);
                }
            }
        }

        return $rows;
    }
}
