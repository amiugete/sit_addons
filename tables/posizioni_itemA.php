<?php
require_once '../session.php';
$res_ok=0;
#require('../validate_input.php');
header('Content-Type: application/json; charset=utf-8');



require_once '../conn_ok.php';
//echo "OK";


if(!$conn) {
    die('Connessione fallita !<br />');
} else {

    $query_pos="
select mi.sportello,
mi.data_installazione, 
case
	when p.data_ora >= NOW() - INTERVAL '24 hours'
        THEN TRUE
        ELSE FALSE
    END AS posizione_ultime_24h,
p.data_ora as last_update,
ST_Y(p.geoloc::geometry) as lat,
ST_X(p.geoloc::geometry) as lon
from tellus.mezzi_itemab mi 
LEFT JOIN LATERAL (
    SELECT t.data_ora, t.geoloc 
    FROM tellus.posizioni_itemab t 
    WHERE t.sportello = mi.sportello
    ORDER BY data_ora DESC
    LIMIT 1
) p ON TRUE
where p.geoloc is not null";
    
    $result = pg_prepare($conn, "query_pos", $query_pos);
    if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
    $result = pg_execute($conn, "query_pos", array());
    
        if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
   


    $rows = array();
    //echo "<br>ok fino a qua";
    if ($result === false) {
        die("ERRORE EXECUTE: ".pg_last_error($conn));
    }

    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }

if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
   echo '[{"NOTE":"No data"}]';
}

require_once("./json_no_paginazione.php");

}


?>