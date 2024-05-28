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

//oci_bind_by_name($result3, ':p1', $turno);
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