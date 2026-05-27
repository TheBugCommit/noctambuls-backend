#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * NOCTAMBULS – generador de dades de seed
 *
 * Genera db_seed.sql amb:
 *   - 10 auth_users + 10 profiles
 *   - 50 censuses (40 PUBLISHED, 10 DRAFT)
 *   - 10.000 sessions (400 Census, 3.600 Station, 6.000 Incidental)
 *   - ~18.000 observations (70 % de les sessions)
 *   - ~12.000 session_participants (60 % de les sessions)
 *
 * Ús: php bin/generate-seed.php
 */

$now        = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$outputPath = dirname(__DIR__) . '/db_seed.sql';

// ============================================================
// HELPERS
// ============================================================

function uuid(): string
{
    $d    = random_bytes(16);
    $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
    $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

function q(?string $v): string
{
    if ($v === null) {
        return 'NULL';
    }
    return "'" . addslashes($v) . "'";
}

function dt(?DateTimeImmutable $d): string
{
    return $d !== null ? "'" . $d->format('Y-m-d H:i:s') . "'" : 'NULL';
}

/** Data/hora aleatòria dins de l'any $year, mai superior a $max */
function randDt(int $year, DateTimeImmutable $max): DateTimeImmutable
{
    $from = new DateTimeImmutable("{$year}-01-01 00:00:00", new DateTimeZone('UTC'));
    $to   = $year < (int) $max->format('Y')
        ? new DateTimeImmutable("{$year}-12-31 23:59:59", new DateTimeZone('UTC'))
        : $max;

    if ($to->getTimestamp() <= $from->getTimestamp()) {
        return $from;
    }

    return (new DateTimeImmutable('@' . random_int($from->getTimestamp(), $to->getTimestamp())))
        ->setTimezone(new DateTimeZone('UTC'));
}

// ============================================================
// ESCRIPTURA PER LOTS
// ============================================================

$fh = fopen($outputPath, 'w');
if ($fh === false) {
    fwrite(STDERR, "ERROR: no s'ha pogut obrir {$outputPath}\n");
    exit(1);
}

function w(string $line): void
{
    global $fh;
    fwrite($fh, $line . "\n");
}

/**
 * Escriu els $rows com INSERTs de màxim $batchSize files per sentència.
 * @param array<array<string>> $rows
 */
function writeInserts(string $table, string $cols, array $rows, int $batchSize = 500): void
{
    if (empty($rows)) {
        return;
    }
    foreach (array_chunk($rows, $batchSize) as $chunk) {
        w("INSERT INTO `{$table}` ({$cols}) VALUES");
        $last = count($chunk) - 1;
        foreach ($chunk as $i => $row) {
            $sep = ($i < $last) ? ',' : ';';
            w('  (' . implode(', ', $row) . ')' . $sep);
        }
    }
}

// ============================================================
// CONSTANTS I TAULES DE REFERÈNCIA
// ============================================================

const NUM_SESSIONS = 10_000;
const NUM_CENSUS_SESSIONS  = 400;   // 10 usuaris × 40 censos publicats
const NUM_STATION_SESSIONS = 3_600;
// La resta fins a 10.000 seran Incidentals (6.000)

const DELETED_SESSION_PCT    = 10; // 10 % de sessions eliminades
const OBS_SESSION_PCT        = 70; // 70 % de sessions amb observacions
const PARTICIPANTS_PCT       = 60; // 60 % de sessions amb participants
const DELETED_OBS_PCT        = 8;  // 8 % d'observacions eliminades

$SPECIES = [
    'a7b8c9d0-e1f2-4a3b-8c7d-6e5f4a3b2c1d',
    'b1c2d3e4-f5a6-4b7c-8d9e-0f1a2b3c4d5e',
    'c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f',
    'c8a2b1f3-5d9e-4c7a-9b8f-3e1d2c4a5b6d',
    'd4e5f6a7-b8c9-4d0e-1f2a-3b4c5d6e7f8a',
    'e2f9a3c1-4b7d-4f8e-9c2b-1d5a6e8b7c9d',
    'e8f9a0b1-c2d3-4e4f-5a6b-7c8d9e0f1a2b',
    'f1d2c3b4-a5e6-4f7d-8c9b-0e1a2b3c4d5e',
];

// Ubicacions reals de Catalunya [lat, lon]
$LOCS = [
    [41.3851, 2.1734],  // Barcelona
    [41.4234, 2.0842],  // Cornellà
    [41.5012, 2.0983],  // Sabadell
    [41.4567, 2.2156],  // Badalona
    [41.9832, 2.8241],  // Girona
    [42.1523, 2.9871],  // Olot zona
    [41.8654, 2.7432],  // Santa Coloma de Farners
    [42.0123, 2.6543],  // Banyoles
    [41.6151, 0.6261],  // Lleida
    [41.7234, 0.8934],  // Balaguer
    [41.5432, 0.4567],  // Alcarràs
    [41.9876, 0.7654],  // Tàrrega
    [41.1189, 1.2445],  // Tarragona
    [41.0543, 1.1234],  // Reus
    [41.2345, 1.3456],  // Valls
    [40.9876, 1.0543],  // Cambrils
    [42.3456, 1.4567],  // Puigcerdà
    [42.5234, 1.6789],  // La Seu d'Urgell
    [42.4567, 1.2345],  // Ripoll
    [42.6123, 1.8901],  // Pont de Suert
    [41.7654, 3.0345],  // Palamós
    [41.8901, 3.1234],  // Platja d'Aro
    [42.0567, 3.2123],  // Roses
    [41.6543, 2.9876],  // Sant Feliu de Guíxols
    [40.7234, 0.8765],  // Tortosa
    [40.6543, 0.7432],  // Amposta
    [40.8901, 0.9876],  // Gandesa
    [40.5678, 0.6543],  // Deltebre
    [41.8234, 1.5678],  // Vic
    [42.1234, 1.9876],  // Berga
    [41.6789, 1.7654],  // Manresa
    [42.0345, 2.3456],  // Ripoll zona
];

$NAMES = [
    'Andreu', 'Berta', 'Carles', 'Dolors', 'Eduard',
    'Fina', 'Gerard', 'Helena', 'Ivan', 'Júlia',
];
$SURNAMES = [
    'Mas', 'Font', 'Vila', 'Puig', 'Roca',
    'Soler', 'Ferrer', 'Martí', 'Costa', 'Prat',
];

$CENSUS_TITLES = [
    'Rapinyaires', 'Ocells Aquàtics', 'Passeriformes', 'Rupícoles',
    'Migració Pre-nupcial', 'Migració Post-nupcial', 'Nocturn',
    'Litoral', 'Muntanya', 'Bosc', 'Aiguamolls', 'Delta',
    'Pirineu', 'Garrotxa', 'Empordà', 'Penedès', 'Ebre',
    'Conca de Barberà', 'Montseny', 'Pla de Lleida',
];
$CENSUS_LOCS = [
    ['Parc del Cadí-Moixeró', 42.25, 1.83],
    ['Delta de l\'Ebre',       40.72, 0.72],
    ['Aiguamolls de l\'Empordà', 42.18, 3.08],
    ['Sant Llorenç del Munt',  41.68, 1.85],
    ['Montseny',               41.78, 2.42],
    ['La Garrotxa',            42.17, 2.52],
    ['Bosc de Poblet',         41.38, 1.05],
    ['Montserrat',             41.61, 1.83],
    ['Cap de Creus',           42.32, 3.31],
    ['Priorat',                41.22, 0.78],
];

// ============================================================
// CAPÇALERA SQL
// ============================================================

w('-- ============================================================');
w('-- NOCTAMBULS SEED DATA');
w('-- Generat: ' . $now->format('Y-m-d H:i:s') . ' UTC');
w('-- Sessions: ' . NUM_SESSIONS . ' | Usuaris: 10 | Censos: 50');
w('-- ============================================================');
w('');
w('SET foreign_key_checks = 0;');
w('SET unique_checks = 0;');
w('SET autocommit = 0;');
w('BEGIN;');
w('');

// ============================================================
// AUTH_USERS + PROFILES
// ============================================================

echo "Generant 10 usuaris i perfils...\n";

$userIds     = [];
$authRows    = [];
$profileRows = [];
$hash        = password_hash('password', PASSWORD_BCRYPT);
$tsNow       = dt($now);

for ($i = 0; $i < 10; $i++) {
    $uid   = uuid();
    $userIds[] = $uid;
    $role  = $i === 0 ? 'ROLE_ADMIN' : 'ROLE_USER';
    $email = strtolower($NAMES[$i]) . '@noctambuls.test';
    $code  = sprintf('#OR-A-%04d', $i + 1);

    $authRows[] = [
        q($uid), q($email), q('internal'),
        $tsNow, q($role), q($hash),
        '0', 'NULL', $tsNow,
    ];
    $profileRows[] = [
        q($uid),
        q($NAMES[$i] . ' ' . $SURNAMES[$i]),
        q(sprintf('+3469%07d', $i + 1)),
        q($code),
        $tsNow, $tsNow,
    ];
}

w('-- =================== AUTH_USERS ===================');
writeInserts(
    'auth_users',
    '`id`, `email`, `provider`, `created_at`, `role`, `password`, `is_blocked`, `anonymized_at`, `updated_at`',
    $authRows
);
w('');

w('-- =================== PROFILES ===================');
writeInserts(
    'profiles',
    '`id`, `name`, `phone`, `ornithologist_code`, `created_at`, `updated_at`',
    $profileRows
);
w('');

// ============================================================
// CENSUSES
// ============================================================

echo "Generant 50 censos...\n";

$publishedCensusIds = [];
$censusRows         = [];

for ($i = 0; $i < 50; $i++) {
    $cid         = uuid();
    $isPublished = $i < 40;
    $status      = $isPublished ? 'PUBLISHED' : 'DRAFT';

    if ($isPublished) {
        $publishedCensusIds[] = $cid;
    }

    $year  = 2022 + ($i % 3);
    $month = str_pad((string) (($i % 12) + 1), 2, '0', STR_PAD_LEFT);
    $dom   = str_pad((string) (1 + ($i % 25)), 2, '0', STR_PAD_LEFT);
    $loc   = $CENSUS_LOCS[$i % count($CENSUS_LOCS)];
    $title = 'Cens ' . $CENSUS_TITLES[$i % count($CENSUS_TITLES)] . ' ' . $year;
    $pubAt = $isPublished ? q("{$year}-{$month}-{$dom} 10:00:00") : 'NULL';
    $hour  = str_pad((string) (6 + ($i % 4)), 2, '0', STR_PAD_LEFT);

    $censusRows[] = [
        q($cid), q($title),
        q("{$year}-{$month}-{$dom}"), q("{$hour}:00:00"),
        q($loc[0]), (string) $loc[1], (string) $loc[2],
        'NULL',
        q($status),
        $tsNow, $tsNow, $pubAt,
        '0', 'NULL',
    ];
}

w('-- =================== CENSUSES ===================');
writeInserts(
    'censuses',
    '`id`, `title`, `date`, `time`, `location_name`, `latitude`, `longitude`, `description`, `status`, `created_at`, `updated_at`, `published_at`, `is_deleted`, `deleted_at`',
    $censusRows
);
w('');

// ============================================================
// SESSIONS + OBSERVACIONS + PARTICIPANTS
// ============================================================

echo "Generant " . NUM_SESSIONS . " sessions...\n";

$sessionRows     = [];
$obsRows         = [];
$participantRows = [];

// Distribució d'anys: 20% 2022, 30% 2023, 30% 2024, 20% 2025
$yearPool = array_merge(
    array_fill(0, 2_000, 2022),
    array_fill(0, 3_000, 2023),
    array_fill(0, 3_000, 2024),
    array_fill(0, 2_000, 2025)
);
shuffle($yearPool);

$sessionCols = implode(', ', [
    '`id`', '`census_id`', '`annotator_id`', '`session_type`',
    '`start_time`', '`end_time`',
    '`temperature`', '`wind`', '`sky`', '`rain`', '`visibility`', '`moon`',
    '`listening_point`', '`latitude`', '`longitude`',
    '`accuracy`', '`altitude`', '`altitude_accuracy`', '`location_source`',
    '`created_at`', '`updated_at`', '`is_deleted`',
]);

$obsCols = implode(', ', [
    '`id`', '`session_id`', '`species_id`',
    '`contact_type`', '`quantity`',
    '`start_time`', '`end_time`',
    '`notes`',
    '`latitude`', '`longitude`', '`accuracy`', '`altitude`', '`altitude_accuracy`', '`location_source`',
    '`bearing`',
    '`created_at`', '`updated_at`',
    '`is_deleted`', '`deleted_at`', '`restored_at`',
]);

$partCols = '`session_id`, `ornithologist_code`';

for ($i = 0; $i < NUM_SESSIONS; $i++) {
    $sid  = uuid();
    $year = $yearPool[$i];

    // ---- Tipus de sessió ----
    if ($i < NUM_CENSUS_SESSIONS) {
        // Census: 10 usuaris × 40 censos = 400 parelles úniques (census_id, annotator_id)
        $type     = 1;
        $userId   = $userIds[$i % 10];
        $censusId = $publishedCensusIds[intdiv($i, 10)];
        $lp       = random_int(1, 20);
    } elseif ($i < NUM_CENSUS_SESSIONS + NUM_STATION_SESSIONS) {
        $type     = 2;
        $userId   = $userIds[random_int(0, 9)];
        $censusId = null;
        $lp       = random_int(1, 30);
    } else {
        $type     = 0;
        $userId   = $userIds[random_int(0, 9)];
        $censusId = null;
        $lp       = null;
    }

    // ---- Timestamps ----
    $startDt    = randDt($year, $now);
    $maxEndTs   = min($startDt->getTimestamp() + 14_400, $now->getTimestamp() - 60);
    $minEndTs   = $startDt->getTimestamp() + 1_800;
    if ($minEndTs > $maxEndTs) {
        $minEndTs = $maxEndTs;
    }
    $endDt = (new DateTimeImmutable('@' . random_int($minEndTs, $maxEndTs)))
        ->setTimezone(new DateTimeZone('UTC'));

    $isDeleted = random_int(1, 100) <= DELETED_SESSION_PCT ? 1 : 0;

    // ---- Ubicació ----
    $loc = $LOCS[random_int(0, count($LOCS) - 1)];
    $lat = round($loc[0] + random_int(-300, 300) / 100_000, 6);
    $lon = round($loc[1] + random_int(-300, 300) / 100_000, 6);

    $sessionRows[] = [
        q($sid),
        $censusId !== null ? q($censusId) : 'NULL',
        q($userId),
        (string) $type,
        dt($startDt), dt($endDt),
        (string) random_int(0, 4),   // temperature
        (string) random_int(0, 2),   // wind
        (string) random_int(0, 2),   // sky
        (string) random_int(0, 2),   // rain
        (string) random_int(0, 2),   // visibility
        (string) random_int(0, 1),   // moon
        $lp !== null ? (string) $lp : 'NULL',
        (string) $lat,
        (string) $lon,
        (string) random_int(3, 50),  // accuracy (metres)
        (string) random_int(0, 2_500), // altitude (metres)
        'NULL',                       // altitude_accuracy
        (string) random_int(0, 1),   // location_source (0=GPS, 1=Manual)
        dt($startDt), dt($startDt),  // created_at, updated_at
        (string) $isDeleted,
    ];

    // ---- Observacions (70 %) ----
    if (random_int(1, 100) <= OBS_SESSION_PCT) {
        $numObs    = random_int(1, 5);
        $window    = $endDt->getTimestamp() - $startDt->getTimestamp();
        $step      = max(intdiv($window, $numObs), 300);

        for ($j = 0; $j < $numObs; $j++) {
            $oid = uuid();

            $obsStartTs = $startDt->getTimestamp() + $j * $step
                + random_int(0, max(1, $step - 300));
            $obsStartTs = min($obsStartTs, $now->getTimestamp() - 660);

            $obsEndTs = min(
                $obsStartTs + random_int(300, 1_800),
                $endDt->getTimestamp(),
                $now->getTimestamp() - 60
            );
            if ($obsEndTs <= $obsStartTs) {
                $obsEndTs = $obsStartTs + 300;
            }

            $obsDt    = (new DateTimeImmutable('@' . $obsStartTs))->setTimezone(new DateTimeZone('UTC'));
            $obsEndDt = (new DateTimeImmutable('@' . $obsEndTs))->setTimezone(new DateTimeZone('UTC'));

            $isObsDel    = random_int(1, 100) <= DELETED_OBS_PCT ? 1 : 0;
            $obsDeletedAt = $isObsDel ? dt($obsEndDt) : 'NULL';

            $obsRows[] = [
                q($oid), q($sid),
                q($SPECIES[random_int(0, 7)]),
                (string) random_int(0, 1),     // contact_type (0=Visual, 1=Auditive)
                (string) random_int(1, 20),    // quantity
                dt($obsDt), dt($obsEndDt),
                'NULL',                         // notes
                'NULL', 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', // location (nullable)
                (string) round(random_int(0, 3_600) / 10, 1), // bearing 0.0–360.0
                dt($obsDt), dt($obsDt),        // created_at, updated_at
                (string) $isObsDel,
                $obsDeletedAt,
                'NULL',                         // restored_at
            ];
        }
    }

    // ---- Participants (60 %) ----
    if (random_int(1, 100) <= PARTICIPANTS_PCT) {
        $numParts  = random_int(1, 3);
        $usedCodes = [];
        for ($j = 0; $j < $numParts; $j++) {
            $code = sprintf('#OR-A-%04d', random_int(1, 10));
            if (in_array($code, $usedCodes, true)) {
                continue;
            }
            $usedCodes[]     = $code;
            $participantRows[] = [q($sid), q($code)];
        }
    }

    if (($i + 1) % 1_000 === 0) {
        echo sprintf("  %d / %d sessions...\n", $i + 1, NUM_SESSIONS);
    }
}

w('-- =================== SESSIONS ===================');
w('-- 400 Census | 3.600 Station | 6.000 Incidental');
w('-- 10 % eliminades | 70 % amb observacions | 60 % amb participants');
writeInserts('sessions', $sessionCols, $sessionRows);
w('');

w('-- =================== OBSERVATIONS ===================');
writeInserts('observations', $obsCols, $obsRows);
w('');

w('-- =================== SESSION_PARTICIPANTS ===================');
writeInserts('session_participants', $partCols, $participantRows);
w('');

// ============================================================
// PEU SQL
// ============================================================

w('COMMIT;');
w('SET foreign_key_checks = 1;');
w('SET unique_checks = 1;');
w('SET autocommit = 1;');
w('');
$totalSessions     = NUM_SESSIONS;
$totalObs          = count($obsRows);  // ja 0 perquè hem bolcat tot als arrays
w('-- FI DEL SEED');

fclose($fh);

$sizeMb = round(filesize($outputPath) / 1_048_576, 2);

echo "\n✓ Fitxer generat: {$outputPath} ({$sizeMb} MB)\n";
echo "\nExecució a la BD:\n";
echo "  docker-compose exec -T mariadb mysql -u\$DB_USER -p\$DB_PASSWORD \$DB_NAME < db_seed.sql\n";
echo "\nVerificació:\n";
echo "  docker-compose exec backend php bin/console doctrine:query:sql \"SELECT COUNT(*) c FROM sessions\"\n";
echo "  docker-compose exec backend php bin/console doctrine:query:sql \"SELECT COUNT(*) c FROM observations\"\n";
