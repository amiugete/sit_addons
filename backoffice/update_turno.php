<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

//echo "Per ora il pulsante salva non fa nulla se non mostrare il turno selezionato e il percorso<br>"; //da commentare


// TURNO
$turno = intval($_POST['turno']);
//echo "turno selezionato: ".$turno."<br>"; //da commentare

$query3="SELECT aft.CODICE_TURNO, at2.DURATA
FROM ANAGR_TURNI at2
JOIN ANAGR_FASCIA_TURNO aft ON aft.FASCIA_TURNO = at2.FASCIA_TURNO 
WHERE DTA_DISATTIVAZIONE > SYSDATE AND at2.ID_TURNO = :p1";


$result3 = oci_parse($oraconn, $query3);
oci_bind_by_name($result3, ':p1', $turno);
oci_execute($result3);
while($r3 = oci_fetch_assoc($result3)) { 
  $id_turno=$r3['CODICE_TURNO'];
  $durata=$r3['DURATA'];
}

//echo "id turno: ".$id_turno."<br>";

oci_free_statement($result3);
//echo "durata: ".$durata."<br>";;

$cod_percorso = $_POST['id_percorso'];
//echo "percorso: ".$cod_percorso."<br>"; //da commentare

$vers = intval($_POST['old_vers']);
//echo "versione: ".$vers."<br>";

//exit();

// update turno su UO
$update_uo= "UPDATE ANAGR_SER_PER_UO aspu
SET ID_TURNO = :c0
WHERE ID_PERCORSO = :c1 
AND DTA_ATTIVAZIONE > SYSDATE or DTA_ATTIVAZIONE is null ";

$result_uo0 = oci_parse($oraconn, $update_uo);
# passo i parametri
oci_bind_by_name($result_uo0, ':c0', $turno);
oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
oci_execute($result_uo0);
oci_free_statement($result_uo0);




// update turno su SIT elem.percorsi
$update_sit0="UPDATE elem.percorsi p
SET id_turno = $1
where cod_percorso LIKE $2 and (data_attivazione > now() or data_dismissione is null or data_attivazione is null )";

$result_usit0 = pg_prepare($conn, "update_sit0", $update_sit0);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn, "update_sit0", array($turno, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}




// update turno su SIT anagrafe_percorsi.elenco_percorsi
$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET id_turno = $1, data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_inizio_validita > now()";


$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}


$result_usit1 = pg_execute($conn, "update_sit1", array($turno, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}


// update turno su SIT anagrafe_percorsi.percorsi_ut
$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET id_turno = $1
where cod_percorso LIKE $2 and data_attivazione > now()";


$result_usit3 = pg_prepare($conn, "update_sit3", $update_sit3);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}


$result_usit3 = pg_execute($conn, "update_sit3", array($turno, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}


if ($res_ok==0){
    echo '<font color="green"> Nuovo turno salvato correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>