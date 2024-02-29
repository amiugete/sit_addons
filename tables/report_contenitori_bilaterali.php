<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$conn) {
    die('Connessione fallita !<br />');
} else {


    require_once ('./query_contenitori_bilaterali.php');
    
    if($_GET['ut']) {
        $result = pg_execute($conn, "my_query", array($_GET['ut']));  
    } else {
        $result = pg_execute($conn, "my_query", array());
    }

   

    $rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }
    
    
    //pg_close($conn);
	#echo $rows ;
	if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}


?>