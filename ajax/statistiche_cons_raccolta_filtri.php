
<?php

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../conn_ok.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT
        (
            SELECT json_agg(valore ORDER BY valore)
            FROM (
                SELECT DISTINCT comune AS valore
                FROM consunt.mv_report_raccolta_giorno
                WHERE comune IS NOT NULL
            ) c
        ) AS comuni,
        (
            SELECT json_agg(valore ORDER BY valore)
            FROM (
                SELECT DISTINCT zona_territoriale AS valore
                FROM consunt.mv_report_raccolta_giorno
                WHERE zona_territoriale IS NOT NULL
            ) z
        ) AS zone,
        (
            SELECT json_agg(valore ORDER BY valore)
            FROM (
                SELECT DISTINCT ut AS valore
                FROM consunt.mv_report_raccolta_giorno
                WHERE ut IS NOT NULL
            ) u
        ) AS uts
";

$rs = pg_query($conn, $sql);

if (!$rs) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => pg_last_error($conn)
    ]);

    exit;
}

$filtri = pg_fetch_assoc($rs);

echo json_encode([
    'success' => true,
    'filtri' => [
        'comuni' => json_decode($filtri['comuni'] ?? '[]', true) ?? [],
        'zone'   => json_decode($filtri['zone'] ?? '[]', true) ?? [],
        'uts'    => json_decode($filtri['uts'] ?? '[]', true) ?? []
    ]
], JSON_UNESCAPED_UNICODE);

exit;
