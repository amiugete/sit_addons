<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}



$res_ok=0;


$data_disatt = $_POST['data_disatt'];
//echo $data_disatt."<br>";


//echo $dis_stag."<br>";
//echo $stag_null."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";

$vers = intval($_POST['old_vers']);
//echo $vers."<br>";








//exit();



// update data_disattivazione = domani di quanto attivo fino ad ora

$update_uo= "UPDATE ANAGR_SER_PER_UO aspu
SET DTA_DISATTIVAZIONE = to_date(:c0, 'DD/MM/YYYY') 
WHERE ID_PERCORSO = :c1 
AND DTA_DISATTIVAZIONE > SYSDATE";

$result_uo0 = oci_parse($oraconn, $update_uo);
# passo i parametri
oci_bind_by_name($result_uo0, ':c0', $data_disatt);
oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
oci_execute($result_uo0);
oci_free_statement($result_uo0);


// controllo se in caso di percorso stagionale si vuole proprio dismettere il percorso (id_categoria_uso = 4), 
// // se true allora faccio update di stagionalita = null in modo che venga intercettato dallo spoon che disattiva i percorsi
if ($_POST['dis_stag']){
    $dis_stag = $_POST['dis_stag'];
    $update_sit0="UPDATE elem.percorsi epo
    SET data_dismissione=To_DATE($1, 'DD/MM/YYYY'), stagionalita = null
    where cod_percorso LIKE $2 and (data_dismissione > now() or data_dismissione is null)";
} else {
    $dis_stag = 'f';
    $update_sit0="UPDATE elem.percorsi epo
    SET data_dismissione=To_DATE($1, 'DD/MM/YYYY')
    where cod_percorso LIKE $2 and (data_dismissione > now() or data_dismissione is null)";
}

$result_usit0 = pg_prepare($conn, "update_sit0", $update_sit0);

if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn, "update_sit0", array($data_disatt, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_ut<br>";


$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET data_fine_validita= To_DATE($1, 'DD/MM/YYYY'), data_ultima_modifica=now()
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit1 = pg_execute($conn, "update_sit1", array($data_disatt, $cod_percorso)); 

if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>";


$update_sit2="UPDATE anagrafe_percorsi.elenco_percorsi_old epo
SET data_fine_validita= To_DATE($1, 'DD/MM/YYYY') 
where cod_percorso LIKE $2 and data_fine_validita > now()";


$result_usit2 = pg_prepare($conn, "update_sit2", $update_sit2);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit2 = pg_execute($conn, "update_sit2", array($data_disatt, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>";


$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET data_disattivazione= To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_disattivazione > now()";

$result_usit3 = pg_prepare($conn, "update_sit3", $update_sit3);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit3 = pg_execute($conn, "update_sit3", array($data_disatt, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
//echo "<br><br>Update anagrafe_percorsi.percorsi_ut<br>";



$update_sit4="UPDATE anagrafe_percorsi.date_percorsi_sit_uo dps
SET data_fine_validita = To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit4 = pg_prepare($conn, "update_sit4", $update_sit4);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit4 = pg_execute($conn, "update_sit4", array($data_disatt, $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}

//echo "<br><br>Update anagrafe_percorsi.percordate_percorsi_sit_uo<br>";


if ($res_ok==0){
    echo '<font color="green"> Data disattivazione salvata correttamente!</font>';
} else {
    echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}   

#exit();
#header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$vers."");


?>
