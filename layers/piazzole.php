<?php
require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');

error_log("INIZIO piazzole.php");


require_once '../conn_ok.php';
//echo "OK";

// recupero i parametri della bounding box dalla richiesta GET
$xmin = isset($_GET['xmin']) ? floatval($_GET['xmin']) : null;
$ymin = isset($_GET['ymin']) ? floatval($_GET['ymin']) : null;
$xmax = isset($_GET['xmax']) ? floatval($_GET['xmax']) : null;
$ymax = isset($_GET['ymax']) ? floatval($_GET['ymax']) : null;


$id_comune = isset($_GET['id_comune']) ? intval($_GET['id_comune']) : null;
$id_via = isset($_GET['id_via']) ? intval($_GET['id_via']) : null;



//echo $filter; 
//exit();
//echo ini_get('error_log');

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

   // costruisco dinamicamente solo le clausole necessarie, con parametri veri
    $params = array($xmin, $ymin, $xmax, $ymax);
    $filter = '';

    if ($id_comune) {
        $params[] = $id_comune;
        $filter .= ' AND v.id_comune = $' . count($params) . ' ';
    }
    if ($id_via) {
        $params[] = $id_via;
        $filter .= ' AND b.id_via = $' . count($params) . ' ';
    }

    $query_pos = "
    WITH base AS MATERIALIZED (
    SELECT p.id_piazzola, 
        p.id_asta,
        p.ecopunto,
        p.suolo_privato,
        g.geoloc, a.id_via, 
        a.id_ut, a.id_circoscrizione, a.id_quartiere
    FROM elem.piazzole p
    JOIN geo.piazzola g ON g.id = p.id_piazzola
    JOIN elem.aste a ON a.id_asta = p.id_asta
    WHERE p.data_eliminazione IS NULL
    AND g.geoloc && ST_Transform(ST_MakeEnvelope(\$1,\$2,\$3,\$4,4326), ST_SRID(g.geoloc))
)
SELECT b.id_piazzola,
       v.id_comune,
       b.id_ut,
       b.id_circoscrizione AS id_municipio,
       b.id_quartiere,
       v.id_via,
       b.id_asta,
       COALESCE(pap.is_pap,0) AS is_pap,
       b.ecopunto,
       b.suolo_privato,
       pap.rifiuti,
       pap.colore_piazzola,
       ST_AsGeoJSON(ST_Transform(b.geoloc,4326)::geometry) AS geometry
FROM base b
JOIN topo.vie v ON v.id_via = b.id_via
LEFT JOIN LATERAL (SELECT
        CASE WHEN COUNT(*) = COUNT(ep.id_elemento) THEN 1 ELSE 0 END AS is_pap,
        CASE WHEN COUNT(DISTINCT tr.colore_piazzola) = 1 THEN MAX(tr.colore_piazzola) ELSE NULL END AS colore_piazzola,
        jsonb_agg(DISTINCT jsonb_build_object(
            'id', te.tipo_rifiuto,
            'ordine', tr.ordinamento,
            'nome', tr.nome_stampa
        )) AS rifiuti
    FROM elem.elementi e
    JOIN elem.tipi_elemento te ON te.tipo_elemento = e.tipo_elemento
    JOIN elem.tipi_rifiuto tr ON tr.tipo_rifiuto = te.tipo_rifiuto
    LEFT JOIN elem.elementi_privati ep ON ep.id_elemento = e.id_elemento
    WHERE e.id_piazzola = b.id_piazzola) pap ON true  
    WHERE 1=1 ". $filter.";";

    //echo "BBOX: xmin=$xmin ymin=$ymin xmax=$xmax ymax=$ymax id_comune=" . var_export($id_comune, true) . " id_via=" . var_export($id_via, true);
    //echo $query_pos;
    //exit(); 
    $t0 = microtime(true);

    $result = pg_query_params($conn_sit, $query_pos, $params);
    //echo "<br>ok fino a qua";
    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn_sit));
    }


    $t1 = microtime(true);

    $geojson=array(
        'type' => 'FeatureCollection',
        'features' => array()
    );
    //echo "Inizio ciclo while per aggiungere le features...\n";
    require_once './geojson.php';


    while($r = pg_fetch_assoc($result)) {
        // Aggiungi la feature usando la funzione geojsonFeature
        //echo "Aggiungo feature con id_piazzola: ".$r['id_piazzola']."\n";
        $geojson['features'][] = geojsonFeature($r['geometry'], [
                'id_piazzola' => $r['id_piazzola'],
                'id_comune' => $r['id_comune'],
                'id_ut' => $r['id_ut'],
                'id_municipio' => $r['id_municipio'],
                'id_quartiere' => $r['id_quartiere'],
                'id_via' => $r['id_via'],
                'id_asta' => $r['id_asta'],
                /*'via' => $r['via'],
                'riferimento' => $r['riferimento'],
                'numero_civico' => $r['numero_civico'],*/
                'rifiuti' => json_decode($r['rifiuti'], true),
                'colore_piazzola' => $r['colore_piazzola'] ?? $colore_piazzola,
                'is_pap' => $r['is_pap'],
                'ecopunto' => $r['ecopunto'],
                'suolo_privato' => $r['suolo_privato']
        ]);

    }

    $t2 = microtime(true);
    echo json_encode($geojson);

    $t3 = microtime(true);

}


/*error_log(sprintf(
    "SQL %.3fs - BUILD %.3fs - JSON %.3fs",
    $t1-$t0,
    $t2-$t1,
    $t3-$t2
));*/
?>