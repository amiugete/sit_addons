<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";


if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    
    $query="select c.id_comune, c.descr_comune  from topo.comuni c
        where c.gestito_sit = 'S'";

 
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
    $result = pg_execute($conn_sit, "query0", array());  
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