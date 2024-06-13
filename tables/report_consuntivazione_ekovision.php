<?php
session_start();
#require('../validate_input.php');




if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
    
require_once ('./query_consuntivazione_ekovision.php');

//$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA >= to_date('20240428', 'YYYYMMDD' )". $query1;

//da primo giorno del mese prima
//$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA between add_months(trunc(sysdate,'mm'),-1) 
//and trunc(sysdate,'mm')". $query1;

// ultimo mese
if($_GET['data_inizio']) {
    $query= $query0 ." WHERE vrpe.DATA_PIANIFICATA between to_date(:d1 ,'YYYY-MM-DD') AND to_date(:d2 ,'YYYY-MM-DD')". $query1; ;  
} else {
    $query = $query0 ." WHERE vrpe.DATA_PIANIFICATA >= (to_date(CURRENT_DATE) - INTERVAL '1' month) ". $query1;
}

//echo $query;
//echo "<br>";

if($_GET['ut']) {
    $query= "select * from (".$query.") a where :u1 = any(ID_UTS) " ;  
} else {
    $query=$query;
    //$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA >= to_date('20240428', 'YYYYMMDD' ) and au.ID_UO in (2,4)". $query1;
}

//echo $query;
//echo "<br>";





$result = oci_parse($oraconn, $query);


if($_GET['ut']) {
    oci_bind_by_name($result, ':u1', $_GET['ut']);
}
if($_GET['data_inizio']) {
    oci_bind_by_name($result, ':d1', $_GET['data_inizio']);
    oci_bind_by_name($result, ':d2', $_GET['data_fine']);
}

oci_execute($result);
$rows = array();
while($r = oci_fetch_assoc($result)) { 
    //print $r;
    $rows[] = $r;
}
oci_free_statement($result);
oci_close($oraconn);
//pg_close($conn);
#echo $rows ;
if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
    echo "[{\"NOTE\":'No data'}]";
}

if ($json){
    echo $json;
} else {
    echo json_last_error_msg();
}

exit(0);
}


?>