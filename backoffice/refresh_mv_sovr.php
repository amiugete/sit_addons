<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

/*$query_refresh="REFRESH MATERIALIZED VIEW sovrariempimenti.mv_report_piazzole_da_analizzare";
$result = pg_prepare($conn_sovr, "query_refresh", $query_refresh);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    echo pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query_refresh", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    echo pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}*/

// Imposta schema corretto

#pg_query($conn_sovr, "SET search_path TO public");
$sql = "SELECT sovrariempimenti.refresh_mv_report_piazzole_sovr()";

$result = pg_query($conn_sovr, $sql);

if ($result === false) {
    echo pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
} else {
    echo "Refresh OK";
    $res_ok=0;
}






if ($res_ok==0){
    echo '<font color="green"> Dati aggiornati correttamente!</font>';
    http_response_code(200);
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
    http_response_code(400);
}

?>