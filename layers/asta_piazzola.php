<?php

require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";


$id_asta = $_GET['id_asta'];
$buffer = 20;


if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {
    $query_asta="
    select 
    a.id_asta,
    ST_AsGeoJSON(ST_Transform(g.geoloc, 4326))::json AS geometry,
    ST_AsGeoJSON(ST_Transform(ST_Buffer(g.geoloc, $1), 4326))::json AS buffer
    from elem.aste a
    join geo.grafostradale g on g.id = a.id_asta
    WHERE a.id_asta = $2
    ";
    


    $result = pg_prepare($conn_sit, "query_asta", $query_asta);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn_sit, "query_asta", array($buffer, $id_asta));
    
        if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
   


    $features = [];

    while ($row = pg_fetch_assoc($result)) {

        // Asta
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'id_asta' => (int)$row['id_asta'],
                'tipo' => 'asta'
            ],
            'geometry' => json_decode($row['geometry'])
        ];

        // Buffer 20 metri
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'id_asta' => (int)$row['id_asta'],
                'tipo' => 'buffer'
            ],
            'geometry' => json_decode($row['buffer'])
        ];
    }

    echo json_encode([
        'type' => 'FeatureCollection',
        'features' => $features
    ]);

}


?>