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

$filter="WHERE 1=1 ";
if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 

    $query_targhe="
select u.descrizione,
mdt.id_uo, mdt.targa, mdt.quintali, mdt.in_uso, 
mdt.data_inserimento 
from  etl.mezzi_ditte_terze mdt
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = mdt.id_uo 
join topo.ut u on u.id_ut= cmu.id_uo_sit ".$filter;
    
    $result = pg_prepare($conn, "my_query", $query_targhe);
    $result = pg_execute($conn, "my_query", array());
    
   

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