<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

echo "Per ora il pulsante salva non fa nulla se non mostrare la stagione selezionata e il percorso<br>"; 


// stagione
$stag =  $_POST['stag'];
if($stag!=''){
  $switchOng = str_pad($_POST['switchong'], 2, "0", STR_PAD_LEFT);
  $switchOnm = str_pad($_POST['switchonm'], 2, "0", STR_PAD_LEFT);
  $switchON = $switchOng.$switchOnm;
  $switchOffg = str_pad($_POST['switchoffg'], 2, "0", STR_PAD_LEFT);
  $switchOffm = str_pad($_POST['switchoffm'], 2, "0", STR_PAD_LEFT);
  $switchOFF = $switchOffg.$switchOffm;
}else{
  $stag = null;
  $switchON = null;
  $switchOFF = null;
}
//echo "stagione: ".$stag."<br>"; 

//echo "switch on: ".$switchON."<br>";

//oci_free_statement($result3);
//echo "switch off: ".$switchOFF."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo "percorso: ".$cod_percorso."<br>"; 

$vers = intval($_POST['old_vers']);
//echo "versione: ".$vers."<br>";

//exit();

// update turno su SIT elem.percorsi
$update_sit0="UPDATE elem.percorsi p
SET stagionalita = $1,
ddmm_switch_on = $2,
ddmm_switch_off = $3
where cod_percorso LIKE $4 and (data_attivazione > now() or data_dismissione is null or data_attivazione is null )";

$result_usit0 = pg_prepare($conn_sit, "update_sit0", $update_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update elem.percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn_sit, "update_sit0", array($stag, $switchON, $switchOFF, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update elem.percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

// update turno su SIT anagrafe_percorsi.elenco_percorsi
$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET stagionalita = $1,
ddmm_switch_on = $2,
ddmm_switch_off = $3 
data_ultima_modifica=now() 
where cod_percorso LIKE $4 and data_inizio_validita > now()";


$result_usit1 = pg_prepare($conn_sit, "update_sit1", $update_sit1);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


$result_usit1 = pg_execute($conn_sit, "update_sit1", array($stag, $switchON, $switchOFF, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



if ($res_ok==0){
    echo '<font color="green"> Nuova stagionalità salvata correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>