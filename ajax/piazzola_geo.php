<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";




$id_piazzola = isset($_GET['id']) ? intval($_GET['id']) : null;

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {


    $query0="SELECT 
st_y(st_transform(p.geoloc,4326)) as lat,
st_x(st_transform(p.geoloc,4326)) as lon
from geo.piazzola p 
where id = $1";

 


    $result = pg_prepare($conn_sit, "query0", $query0);

    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 2";
    $result = pg_execute($conn_sit, "query0", array($id_piazzola));  
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
            

    //echo "sono qua!";
    require_once "../tables/json_no_paginazione.php";



    exit(0);
}


?>