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

$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");


if ($_GET['s']){
    $d1=$_GET['s'];
} else {
    $d1=$last_month->format('Y-m-d');
}


if ($_GET['e']){
    $d2=$_GET['e'];
} else {
    $d2=$today->format('Y-m-d');
}

$filter="WHERE 1=1 ";

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter. " AND ".$key." LIKE '%".$val."%' ";
        //} 
         
    }
} 

#echo $filter;
#echo '<br>';

#exit();

//echo $d1 ."<br" .$d2;
//exit();

if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
 
    
$query0="/* report indicatori raccolta */
        SELECT * FROM (SELECT AMBITO, COMUNE, substr(ANNOMESE, 1,4) AS ANNO, substr(ANNOMESE, 5,2) AS MESE, SERVIZI_PIANIFICATI,
        SERVIZI_NON_EFFETTUATI, CAUSA_FORZA_MAGGIORE, IMPUTABILI_UTENTE, IMPUTABILI_GESTORE, ALTRO,
         PERC_SERV_EFFETTUATI, PERC_SERV_NON_EFFETTUATI 
         FROM UNIOPE.INDICATORI_ARERA_RACCOLTA viar) viar ".$filter. " 
        ORDER BY ANNO, MESE, AMBITO, COMUNE  ";







#$query= $query0 .' '. $query1;

#echo $page_n;
#echo "<br>";
#echo $page_size;
#echo $query0;
#exit();

$result = oci_parse($oraconn, $query0);


#oci_bind_by_name($result, ':d1', $d1);
#oci_bind_by_name($result, ':d2', $d2);
#oci_bind_by_name($result, ':o3', $page_size);



/*if($_GET['ut']) {
    oci_bind_by_name($result, ':u1', $_GET['ut']);
}
if($_GET['data_inizio']) {
    oci_bind_by_name($result, ':d1', $_GET['data_inizio']);
    oci_bind_by_name($result, ':d2', $_GET['data_fine']);
}*/

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


require_once("./json_paginazione.php");



exit(0);
}


?>