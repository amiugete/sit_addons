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


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    $query_pos="
    select 
    a.id_asta,
    v.id_comune, 
    a.id_circoscrizione as id_municipio, 
    a.id_quartiere, 
    a.id_ut,
    a.id_via,
    ST_AsGeoJSON(ST_Transform(g.geoloc,4326)::geometry) as geometry
    from elem.aste a
    join topo.vie v on v.id_via = a.id_via 
    join geo.grafostradale g on g.id = a.id_asta
    /* VERIFICO CHE LA GEOMETRIA SIA NELLA BBOX*/
    WHERE
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
    ORDER BY a.id_asta";
    


    $result = pg_prepare($conn, "query_pos", $query_pos);
    if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn, "query_pos", array($xmin, $ymin, $xmax, $ymax));
    
        if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
   


    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn));
    }



    $geojson=array(
        'type' => 'FeatureCollection',
        'features' => array()
    );

    require_once './geojson.php';


    while($r = pg_fetch_assoc($result)) {
        // Aggiungi la feature usando la funzione geojsonFeature
        $geojson['features'][] = geojsonFeature($r['geometry'], [
                'id_asta' => $r['id_asta'],
                'id_comune' => $r['id_comune'],
                'id_municipio' => $r['id_municipio'],
                'id_quartiere' => $r['id_quartiere'],
                'id_ut' => $r['id_ut'],
                'id_via' => $r['id_via']
        ]);

    }


    echo json_encode($geojson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

}


?>