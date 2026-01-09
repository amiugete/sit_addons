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

$mezzo = $_POST['mezzo_ut'];
//echo "mezzo selezionato: ".$mezzo."<br>";

$query_fammezzo="select cdaog3,
categoria  
from elem.automezzi a 
where cdaog3= $1"; 
$result_fammezzo = pg_prepare($conn_sit, "query_fammezzo", $query_fammezzo);
$result_fammezzo = pg_execute($conn_sit, "query_fammezzo", array($mezzo));  
//echo $query1;    
while($rfm = pg_fetch_assoc($result_fammezzo)) { 
  $automezzo=$rfm['categoria'];
}
//echo $automezzo."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo "percorso: ".$cod_percorso."<br>"; 

$vers = intval($_POST['old_vers']);
//echo "versione: ".$vers."<br>";

//exit();
if ($checkTest == 0){
    // update turno su UO
    $update_uo= "UPDATE ANAGR_SER_PER_UO aspu
    SET FAM_MEZZO = :c0
    WHERE ID_PERCORSO = :c1 
    AND DTA_ATTIVAZIONE > SYSDATE or DTA_ATTIVAZIONE is null ";

    $result_uo0 = oci_parse($oraconn, $update_uo);
    # passo i parametri
    oci_bind_by_name($result_uo0, ':c0', $automezzo);
    oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
    oci_execute($result_uo0);
    oci_free_statement($result_uo0);
}

// update turno su SIT elem.percorsi
$update_sit0="UPDATE elem.percorsi p
SET famiglia_mezzo = $1
where cod_percorso LIKE $2 and (data_attivazione > now() or data_dismissione is null or data_attivazione is null )";

$result_usit0 = pg_prepare($conn_sit, "update_sit0", $update_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn_sit, "update_sit0", array($mezzo, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

// update turno su SIT anagrafe_percorsi.percorsi_ut
$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET cdaog3 = $1
where cod_percorso LIKE $2 and data_attivazione > now()";


$result_usit3 = pg_prepare($conn_sit, "update_sit3", $update_sit3);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_usit3 = pg_execute($conn_sit, "update_sit3", array($mezzo, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


if ($res_ok==0){
    echo '<font color="green"> Nuovo mezzo salvato correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>