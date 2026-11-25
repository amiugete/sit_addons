
<?php

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../conn_ok.php';

header('Content-Type: application/json; charset=utf-8');

// =====================================================
// PARAMETRI
// =====================================================

$data_da = $_GET['data_da'] ?? null;
$data_a  = $_GET['data_a'] ?? null;

$comune = trim($_GET['comune'] ?? '');
$zona   = trim($_GET['zona'] ?? '');
$ut     = trim($_GET['ut'] ?? '');

$comune = $comune !== '' ? $comune : null;
$zona   = $zona !== '' ? $zona : null;
$ut     = $ut !== '' ? $ut : null;

$solo_arera = filter_var(
    $_GET['solo_arera'] ?? false,
    FILTER_VALIDATE_BOOLEAN
);

$escludi_non_previsti = filter_var(
    $_GET['escludi_non_previsti'] ?? false,
    FILTER_VALIDATE_BOOLEAN
);

$escludi_causali_non_previste = filter_var(
    $_GET['escludi_causali_non_previste'] ?? false,
    FILTER_VALIDATE_BOOLEAN
);

// =====================================================
// VALIDAZIONE DATE
// =====================================================

if (
    !$data_da ||
    !$data_a ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_da) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_a) ||
    $data_da > $data_a
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => 'Intervallo date non valido'
    ]);

    exit;
}

// =====================================================
// PARAMETRI SQL
// =====================================================

$params = [
    $data_da,
    $data_a,
    $zona,
    $ut,
    $comune,
    $solo_arera ? 'true' : 'false',
    $escludi_non_previsti ? 'true' : 'false',
    $escludi_causali_non_previste ? 'true' : 'false'
];

// =====================================================
// CTE COMUNE
// =====================================================

$sql = "
WITH dati AS (
    SELECT *
    FROM consunt.mv_report_raccolta_giorno
    WHERE data_programmata
          BETWEEN to_date($1, 'YYYY-MM-DD')::date AND to_date($2, 'YYYY-MM-DD')::date
      AND (
          $3::text IS NULL
          OR zona_territoriale = $3::text
      )
      AND (
          $4::text IS NULL
          OR ut = $4::text
      )
      AND (
          $5::text IS NULL
          OR comune = $5::text
      )
      AND (
          $6::boolean = false
          OR gestione_arera = true
      )
      AND (
          $7::boolean = false
          OR non_prev IS NULL
      )
      AND (
          $8::boolean = false
          OR COALESCE(lower(causale), '') NOT IN (
              'festivo',
              'non previsto'
          )
      )
),
/* =====================================================
-- KPI
-- =====================================================*/
kpi AS (
    SELECT
        COALESCE(SUM(numero_eventi), 0) AS servizi,
        COALESCE(
            SUM(
                CASE
                    WHEN esito <> 'Fatto'
                    THEN numero_eventi
                    ELSE 0
                END
            ),
            0
        ) AS disservizi,
        ROUND(
            100.0 *
            SUM(
                CASE
                    WHEN esito <> 'Fatto'
                    THEN numero_eventi
                    ELSE 0
                END
            )
            /
            NULLIF(SUM(numero_eventi), 0),
            2
        ) AS perc_disservizi,

        COUNT(DISTINCT causale) AS causali
    FROM dati
),
/* =====================================================
-- GRAFICO 1: DISSERVIZI MENSILI
-- =====================================================*/
grafico_disservizi_mese AS (
    SELECT
        anno,
        mese,
        SUM(
            CASE
                WHEN esito <> 'Fatto'
                THEN numero_eventi
                ELSE 0
            END
        ) AS non_fatti,
        SUM(numero_eventi) AS totale,
        ROUND(
            100.0 *
            SUM(
                CASE
                    WHEN esito <> 'Fatto'
                    THEN numero_eventi
                    ELSE 0
                END
            )
            /
            NULLIF(SUM(numero_eventi), 0),
            2
        ) AS perc_non_fatti
    FROM dati
    GROUP BY anno, mese
),
/* =====================================================
-- GRAFICO 2: CAUSALI MENSILI
-- =====================================================*/
grafico_causali_mese AS (
    SELECT
        anno,
        mese,
        causale,
        ROUND(
            100.0 *
            SUM(numero_eventi)
            /
            SUM(SUM(numero_eventi))
                OVER (PARTITION BY anno, mese),
            2
        ) AS perc_causale
    FROM dati
    WHERE esito <> 'Fatto'
    GROUP BY
        anno,
        mese,
        causale
),
/* =====================================================
-- GRAFICO 3: DISSERVIZI PER GIORNO SETTIMANA
-- =====================================================*/
grafico_dow AS (
    SELECT
        giorno_settimana,
        dow,

        SUM(
            CASE
                WHEN esito <> 'Fatto'
                THEN numero_eventi
                ELSE 0
            END
        ) AS non_fatti,
        SUM(numero_eventi) AS totale,
        ROUND(
            100.0 *
            SUM(
                CASE
                    WHEN esito <> 'Fatto'
                    THEN numero_eventi
                    ELSE 0
                END
            )
            /
            NULLIF(SUM(numero_eventi), 0),
            2
        ) AS perc_non_fatti
    FROM dati

    GROUP BY giorno_settimana, dow
),
/* =====================================================
-- GRAFICO 4: HEATMAP CAUSALI
-- =====================================================*/
grafico_heatmap AS (
    SELECT
        giorno_settimana,
        dow,
        causale,
        ROUND(
            100.0 *
            SUM(numero_eventi)
            /
            SUM(SUM(numero_eventi))
                OVER (PARTITION BY giorno_settimana),
            2
        ) AS perc_causale
    FROM dati
    WHERE esito <> 'Fatto'
    GROUP BY
        giorno_settimana,
        dow,
        causale
)
/* =====================================================
-- RISPOSTA JSON
-- =====================================================*/
SELECT jsonb_build_object(
    'success', true,
    'kpi',
    (
        SELECT to_jsonb(k)
        FROM kpi k
    ),
    'grafici',
    jsonb_build_object(
        'disservizi_mese',
        COALESCE(
            (
                SELECT jsonb_agg(
                    to_jsonb(g)
                    ORDER BY g.anno, g.mese
                )
                FROM grafico_disservizi_mese g
            ),
            '[]'::jsonb
        ),
        'causali_mese',
        COALESCE(
            (
                SELECT jsonb_agg(
                    to_jsonb(g)
                    ORDER BY g.anno, g.mese, g.causale
                )
                FROM grafico_causali_mese g
            ),
            '[]'::jsonb
        ),
        'dow',
        COALESCE(
            (
                SELECT jsonb_agg(
                    to_jsonb(g)
                    ORDER BY g.dow
                )
                FROM grafico_dow g
            ),
            '[]'::jsonb
        ),
        'heatmap',
        COALESCE(
            (
                SELECT jsonb_agg(
                    to_jsonb(g)
                    ORDER BY g.dow, g.causale
                )
                FROM grafico_heatmap g
            ),
            '[]'::jsonb
        )
    )
) AS risultato
";

// =====================================================
// ESECUZIONE
// =====================================================

$rs = pg_query_params($conn, $sql, $params);

if (!$rs) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => pg_last_error($conn)
    ]);

    exit;
}

$row = pg_fetch_assoc($rs);

echo $row['risultato'];

exit;
