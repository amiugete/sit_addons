<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    //echo "CONNESSIONE TEST<br>";
    $checkTest=1;
    require_once ('../conn_test.php');
} else {
    //echo "CONNESSIONE ESERCIZIO<br>";
    $checkTest=0;
    require_once ('../conn.php');
}


$res_ok=0;

//echo "Per ora il pulsante salva non fa nulla se non mostrare il turno selezionato e il percorso<br>"; 


// SQUADRA
$id_ut_sit = intval($_POST['id_ut_sit']);
//echo "ut sit selezionata: ".$id_ut_sit."<br>";

$query_iduo="select id_uo 
from anagrafe_percorsi.cons_mapping_uo cmu 
where id_uo_sit = $1;";
$resultiduo = pg_prepare($conn_sit, "query_iduo", $query_iduo);
$resultiduo = pg_execute($conn_sit, "query_iduo", array($id_ut_sit));  
//echo $query1;    
while($ruo = pg_fetch_assoc($resultiduo)) { 
  $ut_uo=intval($ruo['id_uo']);
}

//echo "ut uo selezionata: ".$ut_uo."<br>";

$squadra = intval($_POST['sq_ut']);
//echo "squadra selezionata: ".$squadra."<br>"; 

$cod_percorso = $_POST['id_percorso'];
//echo "percorso: ".$cod_percorso."<br>"; 

$vers = intval($_POST['old_vers']);
//echo "versione: ".$vers."<br>";

//exit();
if ($checkTest == 0){
    // update turno su UO
    $update_uo= "UPDATE ANAGR_SER_PER_UO aspu
    SET ID_SQUADRA = :c0
    WHERE ID_PERCORSO = :c1 and ID_UO = :c2
    AND DTA_ATTIVAZIONE > SYSDATE or DTA_ATTIVAZIONE is null ";

    $result_uo0 = oci_parse($oraconn, $update_uo);
    # passo i parametri
    oci_bind_by_name($result_uo0, ':c0', $squadra);
    oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
    oci_bind_by_name($result_uo0, ':c2', $ut_uo);
    oci_execute($result_uo0);
    oci_free_statement($result_uo0);
}

// update turno su SIT anagrafe_percorsi.percorsi_ut
$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET id_squadra = $1
where cod_percorso LIKE $2 and data_attivazione > now() and id_ut = $3";


$result_usit3 = pg_prepare($conn_sit, "update_sit3", $update_sit3);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


$result_usit3 = pg_execute($conn_sit, "update_sit3", array($turno, $cod_percorso, $ut_uo)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


if ($res_ok==0){
    echo '<font color="green"> Nuovo turno salvato correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>