<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";

$id_comune=$_GET['id_comune'];
$id_quartiere = isset($_GET['id_quartiere']) ? intval($_GET['id_quartiere']) : null;
$id_municipio = isset($_GET['id_municipio']) ? intval($_GET['id_municipio']) : null;

$filter = "" ;

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    if ($id_quartiere !== null) {
        $filter = " AND id_quartiere  = $id_quartiere";
    }
    if ($id_municipio !== null) {
        $filter = " AND id_municipio  = $id_municipio";
    }
    
    $query="select id_via, nome
from topo.vie v 
where id_comune = $1";

 
    //echo $query0;
    //echo $uos;
    //echo "Sono qua";

    $query0 = $query." ".$filter ;

    $result = pg_prepare($conn_sit, "query0", $query0);

    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 2";
    $result = pg_execute($conn_sit, "query0", array($id_comune));  
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