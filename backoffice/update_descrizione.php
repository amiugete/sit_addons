<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;



$desc = $_POST['desc'];
//echo $desc."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";



$vers = intval($_POST['old_vers']);
//echo $vers."<br>";








//exit();



// update data_disattivazione = domani di quanto attivo fino ad ora

$update_uo= "UPDATE ANAGR_SER_PER_UO aspu
SET DESCRIZIONE = :c0
WHERE ID_PERCORSO = :c1 
AND DTA_DISATTIVAZIONE > SYSDATE";

$result_uo0 = oci_parse($oraconn, $update_uo);
# passo i parametri
oci_bind_by_name($result_uo0, ':c0', $desc);
oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
oci_execute($result_uo0);
oci_free_statement($result_uo0);



$update_sit0="UPDATE elem.percorsi p
SET descrizione = $1
where cod_percorso LIKE $2 and (data_dismissione is null or data_dismissione> now())";

$result_usit0 = pg_prepare($conn, "update_sit0", $update_sit0);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn, "update_sit0", array($desc, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}

//echo "<br><br>Update elem.percorsi<br>";

$descrizione_storico='Nuova descrizione percorso: '.$desc;
$insert_sit0="INSERT INTO util.sys_history (\"type\", \"action\", description, datetime,  id_percorso, id_user)
 VALUES( 'PERCORSO', 'UPDATE', $1 , CURRENT_TIMESTAMP, (select id_percorso from elem.percorsi 
 WHERE cod_percorso LIKE $2 and (data_dismissione is null or data_dismissione> now())), 
 (select id_user from util.sys_users su where \"name\" ilike $3));";

$result_isit0 = pg_prepare($conn, "insert_sit0", $insert_sit0);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_isit0 = pg_execute($conn, "insert_sit0", array($descrizione_storico, $cod_percorso, $_SESSION['username'])); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}

//echo "<br><br>Insert util.sys_history<br>";



$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET descrizione = $1, data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit1 = pg_execute($conn, "update_sit1", array($desc, $cod_percorso)); 

if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>";


$update_sit2="UPDATE anagrafe_percorsi.elenco_percorsi_old epo
SET descrizione = $1
where cod_percorso LIKE $2 and data_fine_validita > now()";


$result_usit2 = pg_prepare($conn, "update_sit2", $update_sit2);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit2 = pg_execute($conn, "update_sit2", array($desc, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>";


/*$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET descrizione = $1
where cod_percorso LIKE $2 and data_disattivazione > now()";

$result_usit3 = pg_prepare($conn, "update_sit3", $update_sit3);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit3 = pg_execute($conn, "update_sit3", array($desc, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}

echo "<br><br>Update anagrafe_percorsi.percorsi_ut<br>";*/


//exit;


//header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$vers."");

if ($res_ok==0){
    echo '<font color="green"> Nuova descrizione salvata correttamente!</font>';
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>