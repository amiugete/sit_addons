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

$filter=" WHERE FAMIGLIA LIKE '%%' ";

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        $filter = $filter." AND UPPER(".$key. ") LIKE UPPER('%".$val."%')";
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
 
    
$query0="/* ora_esecuzione */
        SELECT * from (
            SELECT as2.ID_SERVIZIO_STAMPA, ss.DESCRIZIONE AS famiglia, as2.DESC_SERVIZIO, au.DESC_UO, 
            see.ID_SCHEDA, see.CODICE_SERV_PRED, aspu.DESCRIZIONE, see.DATA_PIANIF_INIZIALE,
            to_char(min(TO_TIMESTAMP(SUBSTR(see.NOMEFILE, 20, 11), 'YYYYMMDD_HH24')), 'DD/MM/YYYY - HH24') AS FASCIA_ORA_esecuzione,
            row_number() over (order by see.DATA_PIANIF_INIZIALE, au.DESC_UO, as2.ID_SERVIZIO_STAMPA, as2.DESC_SERVIZIO) rnk
            FROM SCHEDE_ESEGUITE_EKOVISION see 
            JOIN ANAGR_SER_PER_UO aspu ON aspu.ID_PERCORSO = see.CODICE_SERV_PRED 
                AND to_date(see.DATA_PIANIF_INIZIALE, 'YYYYMMDD') BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
            JOIN anagr_uo au ON au.ID_UO = aspu.ID_UO 
            JOIN ANAGR_SERVIZI as2 ON as2.ID_SERVIZIO = aspu.ID_SERVIZIO 
            JOIN SERVIZIO_STAMPA ss ON ss.ID_SERVZIO_STAMPA = as2.ID_SERVIZIO_STAMPA 
            WHERE au.ID_ZONATERRITORIALE = 7 AND see.DATA_PIANIF_INIZIALE >= '20241021' /*data partenza del sistema*/
            and to_date(see.DATA_PIANIF_INIZIALE, 'YYYYMMDD') between to_date(:d1, 'YYYY-MM-DD') and to_date(:d2,'YYYY-MM-DD') 
            GROUP BY as2.ID_SERVIZIO_STAMPA, as2.DESC_SERVIZIO, au.DESC_UO, 
            see.ID_SCHEDA, see.CODICE_SERV_PRED, see.DATA_PIANIF_INIZIALE, aspu.DESCRIZIONE, ss.DESCRIZIONE  
            order by see.DATA_PIANIF_INIZIALE, au.DESC_UO, as2.ID_SERVIZIO_STAMPA, as2.DESC_SERVIZIO
        ) ".$filter. " order by DATA_PIANIF_INIZIALE, DESC_UO, ID_SERVIZIO_STAMPA, DESC_SERVIZIO
            ";







#$query= $query0 .' '. $query1;

#echo $page_n;
#echo "<br>";
#echo $page_size;
#echo $query0;
#exit();

$result = oci_parse($oraconn, $query0);


oci_bind_by_name($result, ':d1', $d1);
oci_bind_by_name($result, ':d2', $d2);
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