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

require_once('query_piazzole_sovr.php');


$filter="WHERE 1=1 ";
//exit();

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
        if ($key != 'undefined'){
            $filter = $filter. " AND upper(".$key.") LIKE upper('%".$val."%')";
        }
            /* }*/ 
         
    }
} 

//echo $filter;
#echo '<br>';

//exit();

//echo $d1 ."<br" .$d2;
//exit();

if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
 
    
$query0="/* report piazzole sovr */
        select id_piazzola, id_elemento, rif, anno, comune,
        municipio, eliminata, n_ispezioni_anno, segnalazioni, elementi, percorsi
        from (".$query_ps.") ip ".$filter. " 
        ORDER BY anno, comune, id_piazzola  ";


//echo $query0;



$result = pg_prepare($conn_sovr, "query0", $query0);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query0", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 3";


$rows = array();
while($r = pg_fetch_assoc($result)) {
    $rows[] = $r;
    //echo $r['piazzola'];
}


require_once("./json_paginazione.php");



exit(0);
}


?>