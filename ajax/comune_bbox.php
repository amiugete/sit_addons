<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";


$id=(int)$_GET['id'];

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    
    $query="SELECT
    ST_XMin(geom) AS xmin,
    ST_YMin(geom) AS ymin,
    ST_XMax(geom) AS xmax,
    ST_YMax(geom) AS ymax
FROM (
    SELECT ST_Envelope(st_transform(geoloc, 4326)) geom
    FROM geo.confini_comuni_area cca 
    WHERE id = $1
) q";

 
    //echo $query0;
    //echo $uos;
    //echo "Sono qua";

    $query0 = "select * from (".$query.") a where 1=1 ".$filter ;

    $result = pg_prepare($conn_sit, "query0", $query0);

    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 2";
    $result = pg_execute($conn_sit, "query0", array($id));  
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
       echo  pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 3";


    $rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //echo $r['piazzola'];
    }
    //$rows = pg_fetch_assoc($res);
            

    //echo "sono qua!";
    require_once "../tables/json_no_paginazione.php";



    exit(0);
}


?>