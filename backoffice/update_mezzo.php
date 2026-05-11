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

echo "Per ora il pulsante salva non fa nulla se non mostrare il turno selezionato e il percorso<br>"; 


#mezzo

//$text = $_POST["lista_mezzi"];
//echo 'mezzi selezionati: '.$text."<br>";

$codici_mezzi = explode(',', $_POST['lista_mezzi_valori']);
/*foreach($codici_mezzi as $key ){
  echo "Codice: ".$key."<br>";
}*/
if (count($codici_mezzi)>1){
  $cdaog3 = $codici_mezzi[0];
} else {
  $cdaog3 = $codici_mezzi[0];
}

$nomi_mezzi = explode(',', $_POST['lista_mezzi_nomi']);
/*foreach($nomi_mezzi as $key ){
  //echo "Nome mezzi: ".strtoupper(trim(explode('(', $key)[1], ')'))."<br>";
  echo "Nome mezzi: ".strtoupper($key)."<br>";
}*/
if (count($nomi_mezzi)>1){
  $automezzo = implode(' + ', array_map('strtoupper', $nomi_mezzi));
} else {
  $automezzo = strtoupper($nomi_mezzi[0]);
}

 //echo "mezzo per uo: ".$automezzo."<br>";
 //echo "mezzo per percorsi_ut: ".$cdaog3."<br>";

//exit();

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
$result_usit0 = pg_execute($conn_sit, "update_sit0", array($cdaog3, $cod_percorso)); 
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

$result_usit3 = pg_execute($conn_sit, "update_sit3", array($cdaog3, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

// elimino le righe da anagrafe_percorsi.percorsi_mezzi  per percorso e versione e le salvo nuovamente

$dellete_mezzi = "DELETE FROM anagrafe_percorsi.percorsi_mezzi
WHERE cod_percorso LIKE $1 and versione = $2";

$result_delete_mezzi = pg_prepare($conn_sit, "delete_mezzi", $dellete_mezzi);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_delete_mezzi = pg_execute($conn_sit, "delete_mezzi", array($cod_percorso, $vers)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

// inserisco i nuovi mezzi
$insert_mezzi_percorso = "INSERT INTO anagrafe_percorsi.percorsi_mezzi (cod_percorso, versione, id_mezzo) VALUES ($1, $2, $3)";
foreach($codici_mezzi as $i => $id_mezzo){
  $result_mezzi_percorso = pg_prepare($conn_sit, "insert_mezzi_percorso_".$id_mezzo."_".$i, $insert_mezzi_percorso);
  if (pg_last_error($conn_sit)){
    //echo pg_last_error($conn_sit).'<br>';
    $res_ok=$res_ok+1;
  }

  $result_mezzi_percorso = pg_execute($conn_sit, "insert_mezzi_percorso_".$id_mezzo."_".$i, array($cod_percorso, $new_vers, $id_mezzo)); 
  if (pg_last_error($conn_sit)){
    //echo pg_last_error($conn_sit).'<br>';
    $res_ok=$res_ok+1;
  }
}


if ($res_ok==0){
    echo '<font color="green"> Nuovo mezzo salvato correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>