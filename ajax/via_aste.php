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
    id_asta from elem.aste  
    WHERE id_via = $1
    ";


    //$query0 = "select * from (".$query.") a where 1=1 ".$filter ;

    $result = pg_prepare($conn_sit, "query0", $query);

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