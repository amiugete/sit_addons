<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}






$data_att = $_POST['data_att'];
echo $data_att."<br>";

$cod_percorso = $_POST['id_percorso'];
echo $cod_percorso."<br>";



$vers = intval($_POST['old_vers']);
echo $vers."<br>";








//exit();



// update data_disattivazione = domani di quanto attivo fino ad ora

$update_uo= "UPDATE ANAGR_SER_PER_UO aspu
SET DTA_ATTIVAZIONE = to_date(:c0, 'DD/MM/YYYY') 
WHERE ID_PERCORSO = :c1 
AND DTA_ATTIVAZIONE > SYSDATE";

$result_uo0 = oci_parse($oraconn, $update_uo);
# passo i parametri
oci_bind_by_name($result_uo0, ':c0', $data_att);
oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
oci_execute($result_uo0);
oci_free_statement($result_uo0);



$update_sit0="UPDATE elem.percorsi epo
SET data_attivazione=To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and (data_attivazione > now() or data_dismissione is null or data_attivazione is null )";

$result_usit0 = pg_prepare($conn, "update_sit0", $update_sit0);
echo  pg_last_error($conn);
$result_usit0 = pg_execute($conn, "update_sit0", array($data_att, $cod_percorso)); 
echo  pg_last_error($conn);

echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_ut<br>";



$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET data_inizio_validita= To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_inizio_validita > now()";

$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
echo  pg_last_error($conn);
$result_usit1 = pg_execute($conn, "update_sit1", array($data_att, $cod_percorso)); 

echo  pg_last_error($conn);
echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>";


$update_sit2="UPDATE anagrafe_percorsi.elenco_percorsi_old epo
SET data_inizio_validita= To_DATE($1, 'DD/MM/YYYY'), data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_inizio_validita > now()";


$result_usit2 = pg_prepare($conn, "update_sit2", $update_sit2);
echo  pg_last_error($conn);
$result_usit2 = pg_execute($conn, "update_sit2", array($data_att, $cod_percorso)); 
echo  pg_last_error($conn);
echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>";


$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET data_attivazione= To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_attivazione > now()";

$result_usit3 = pg_prepare($conn, "update_sit3", $update_sit3);
echo  pg_last_error($conn);
$result_usit3 = pg_execute($conn, "update_sit3", array($data_att, $cod_percorso)); 
echo  pg_last_error($conn);

echo "<br><br>Update anagrafe_percorsi.percorsi_ut<br>";



$update_sit4="UPDATE anagrafe_percorsi.date_percorsi_sit_uo dps
SET data_inizio_validita = To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_inizio_validita > now()";

$result_usit4 = pg_prepare($conn, "update_sit4", $update_sit4);
echo  pg_last_error($conn);
$result_usit4 = pg_execute($conn, "update_sit4", array($data_att, $cod_percorso)); 
echo  pg_last_error($conn);

echo "<br><br>Update anagrafe_percorsi.percordate_percorsi_sit_uo<br>";



#exit();
header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$vers."");

?>