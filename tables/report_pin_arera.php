<?php
session_start();
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
 
    
$query0="select * from treg_gap.richieste r where scopo ilike '%pronto intervento%' and sottoscopo not ilike 'Chiusura%' ";



$filter='';

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 

$query= $query0.''. $filter.' order by data_telefonata';

$result = pg_prepare($conn, "my_query", $query);
    if (pg_last_error($conn)){
        echo pg_last_error($conn);
        exit;
    }
$result = pg_execute($conn, "my_query", array());

$rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }
    
if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
    echo "[{\"NOTE\":'No data'}]";
}

require_once("./json_paginazione.php");



exit(0);
}


?>