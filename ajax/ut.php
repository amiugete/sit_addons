<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";


if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    $filter = "";
    $query="select /*id_ut,*/ 
cmu.id_uo as id,  
u.descrizione
from topo.ut u
left join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
where u.id_zona in (1,2,3,6)
and u.data_disattivazione is null
and u.ekovision is true
order by 2";

 
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