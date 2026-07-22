<?php
require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";

// recupero i parametri della bounding box dalla richiesta GET
$xmin = isset($_GET['xmin']) ? floatval($_GET['xmin']) : null;
$ymin = isset($_GET['ymin']) ? floatval($_GET['ymin']) : null;
$xmax = isset($_GET['xmax']) ? floatval($_GET['xmax']) : null;
$ymax = isset($_GET['ymax']) ? floatval($_GET['ymax']) : null;


echo ini_get('error_log');

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    $query_pos="
select p.id_piazzola, 
v.id_comune, 
a.id_ut, 
a.id_circoscrizione as id_municipio, 
a.id_quartiere,
v.id_via,
p.id_asta,
COALESCE(pap.is_pap,0) AS is_pap,
p.ecopunto, 
p.suolo_privato,
pap.rifiuti,
pap.colore_piazzola,
/*ST_Y(ST_Transform(g.geoloc,4326)::geometry) as lat,
ST_X(ST_Transform(g.geoloc,4326)::geometry) as lon*/
ST_AsGeoJSON(ST_Transform(g.geoloc,4326)::geometry) as geometry
from elem.piazzole p 
join geo.piazzola g on g.id = p.id_piazzola 
join elem.aste a on a.id_asta = p.id_asta 
join topo.vie v on v.id_via = a.id_via
--left join elem.elementi e on e.id_piazzola = p.id_piazzola 
--left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento 
LEFT JOIN (
    SELECT
        e.id_piazzola,
        CASE
            WHEN COUNT(*) = COUNT(ep.id_elemento)
            THEN 1
            ELSE 0
        END AS is_pap, 
   case 
	   when count(distinct tr.colore_piazzola)=1 then max(tr.colore_piazzola) 
	   else null
   end as colore_piazzola, 
   jsonb_agg(
    DISTINCT jsonb_build_object(
    	'id', te.tipo_rifiuto,
        'ordine', tr.ordinamento,
        'nome', tr.nome_stampa
    )
    --ORDER BY tr.ordinamento
) AS rifiuti
    FROM elem.elementi e
    join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento
    join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
    LEFT JOIN elem.elementi_privati ep
           ON ep.id_elemento = e.id_elemento
    GROUP BY e.id_piazzola
) pap
	ON pap.id_piazzola = p.id_piazzola
WHERE p.data_eliminazione is null
AND 
    g.geoloc &&
    ST_Transform(
        ST_MakeEnvelope(
            $1,
            $2,
            $3,
            $4,
            4326
        ),
        ST_SRID(g.geoloc)
    )
";
    
    $result = pg_prepare($conn_sit, "query_pos", $query_pos);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $t0 = microtime(true);

    $result = pg_execute($conn_sit, "query_pos", array($xmin, $ymin, $xmax, $ymax));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    $rows = array();
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