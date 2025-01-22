<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";

if ($_POST['rim']==$_POST['ut']){
  echo "ATTENZIONE. Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa. Torna indietro e corregg";
  exit();
}

$turno = intval($_POST['turno']);
echo $turno."<br>";

$desc = $_POST['desc'];
echo $desc."<br>";

$cod_percorso = $_POST['id_percorso'];
echo $cod_percorso."<br>";

$freq_uo = $_POST['freq_uo'];
$freq_sit = floor($_POST['freq_sit']);

echo $freq_uo."<br>";
echo $freq_sit."<br>";

$freq_sett = $_POST['freq_sett'];

$id_servizio_uo = intval($_POST['id_servizio_uo']);
$id_servizio_sit = intval($_POST['id_servizio_sit']);
$tipo = $_POST['tipo'];

echo $tipo."<br>";
echo $id_servizio_uo."<br>";
echo $id_servizio_sit."<br>";


if ($_POST['check_SIT']){
  $check_SIT = intval($_POST['check_SIT']);
} else {
  $check_SIT = 0;
}
echo "check_SIT:".$check_SIT."<br>";

#exit();


$durata = intval($_POST['durata']);

echo $durata."<br>";


$data_att = $_POST['data_att'];
$data_disatt = $_POST['data_disatt'];

#mezzo

$cdaog3 = $_POST['cdaog3'];
echo $cdaog3."<br>";


$query0="select cdaog3,
categoria  
from elem.automezzi a 
where cdaog3= $1"; 
$result0 = pg_prepare($conn, "query0", $query0);
$result0 = pg_execute($conn, "query0", array($cdaog3));  
//echo $query1;    
while($r0 = pg_fetch_assoc($result0)) { 
  $automezzo=$r0['categoria'];
}
echo $automezzo."<br>";


#exit();

$insert_uo = "INSERT INTO UNIOPE.ANAGR_SER_PER_UO 
    (ID_SER_PER_UO,
    ID_UO, ID_PERCORSO,
    ID_TURNO, PROG_PERCORSO,
    DTA_ATTIVAZIONE, DTA_DISATTIVAZIONE,
    DURATA, FAM_MEZZO, 
    DESCRIZIONE,
    ID_SERVIZIO, 
    ID_SQUADRA, 
    FROM_SIT, 
    FREQUENZA_NEW, 
    FREQ_SETTIMANE)
    VALUES(
    (SELECT max(id_ser_per_uo)+1 FROM  UNIOPE.ANAGR_SER_PER_UO),
    :p1, :p2,
    :p3, 0, 
    to_date(:p4,'DD/MM/YYYY'), to_date(:p5,'DD/MM/YYYY'),
    :p6, :p7, 
    :p8,
    :p9, 
    :p10,
    1,
    :p11,
    :p12)";



if($_POST['rim']){
    $rim_sit = $_POST['rim'];
    $sq_rim = $_POST['sq_rim'];
    
    #cerco id rimessa UO
    $query1="select id_uo 
    from anagrafe_percorsi.cons_mapping_uo cmu 
    where id_uo_sit = $1;";
    $result1 = pg_prepare($conn, "query1", $query1);
    $result1 = pg_execute($conn, "query1", array($rim_sit));  
    //echo $query1;    
    while($r1 = pg_fetch_assoc($result1)) { 
      $rim_uo=$r1['id_uo'];
    }

    # insert UO 

    
    $result_uo1 = oci_parse($oraconn, $insert_uo);
    # passo i parametri
    oci_bind_by_name($result_uo1, ':p1', $rim_uo);
    oci_bind_by_name($result_uo1, ':p2', $cod_percorso);
    oci_bind_by_name($result_uo1, ':p3', $turno);
    oci_bind_by_name($result_uo1, ':p4', $data_att);
    oci_bind_by_name($result_uo1, ':p5', $data_disatt);
    oci_bind_by_name($result_uo1, ':p6', $durata);
    oci_bind_by_name($result_uo1, ':p7', $automezzo);
    oci_bind_by_name($result_uo1, ':p8', $desc);
    oci_bind_by_name($result_uo1, ':p9', $id_servizio_uo);
    oci_bind_by_name($result_uo1, ':p10', $sq_rim);
    oci_bind_by_name($result_uo1, ':p11', $freq_uo);
    oci_bind_by_name($result_uo1, ':p12', $freq_sett);


    oci_execute($result_uo1);

    oci_free_statement($result_uo1);

}





$ut_sit = intval($_POST['ut']);
$sq_ut = intval($_POST['sq_ut']);

# cerco id ut UO
$query2="select id_uo 
from anagrafe_percorsi.cons_mapping_uo cmu 
where id_uo_sit = $1;";
$result2 = pg_prepare($conn, "query2", $query2);
$result2 = pg_execute($conn, "query2", array($ut_sit));  
//echo $query1;    
while($r2 = pg_fetch_assoc($result2)) { 
  $ut_uo=intval($r2['id_uo']);
}
echo $sq_ut."<br>";
echo $ut_sit."<br>";
echo $ut_uo."<br>";
echo $data_att."<br>";
echo $data_disatt."<br>";


echo $insert_uo."<br>";

#exit();


# INSERT UO 
$result_uo2 = oci_parse($oraconn, $insert_uo);
# passo i parametri
oci_bind_by_name($result_uo2, ':p1', $ut_uo);
oci_bind_by_name($result_uo2, ':p2', $cod_percorso);
oci_bind_by_name($result_uo2, ':p3', $turno);
oci_bind_by_name($result_uo2, ':p4', $data_att);
oci_bind_by_name($result_uo2, ':p5', $data_disatt);
oci_bind_by_name($result_uo2, ':p6', $durata);
oci_bind_by_name($result_uo2, ':p7', $automezzo);
oci_bind_by_name($result_uo2, ':p8', $desc);
oci_bind_by_name($result_uo2, ':p9', $id_servizio_uo);
oci_bind_by_name($result_uo2, ':p10', $sq_ut);
oci_bind_by_name($result_uo2, ':p11', $freq_uo);
oci_bind_by_name($result_uo2, ':p12', $freq_sett);


# commit
$ris=oci_execute($result_uo2);

if (!$ris) {
  echo "<br>ci sono errori<br>";
  $e = oci_error($result_uo2);  // For oci_execute errors pass the statement handle
  echo $e;
  echo $e['message'];
  echo htmlentities($e['message']);
  echo "\n<pre>\n";
  echo htmlentities($e['sqltext']);
  echo "\n%".($e['offset']+1)."s", "^";
  echo  "\n</pre>\n";
}

echo "<br> sono arrivato qua";
oci_free_statement($result_uo2);



if ($id_servizio_sit!=0 and $check_SIT==1){
  # INSERT SIT

  $insert_sit="INSERT INTO elem.percorsi 
  (id_percorso, 
  cod_percorso, 
  versione, id_ut_resp,
  descrizione, ente_effettuante,
  famiglia_mezzo, id_turno, 
  id_categoria_uso,
  attivatore, data_attivazione,
  frequenza,
  stagionalita,
  id_squadra, 
  ddmm_switch_on, 
  ddmm_switch_off, 
  id_servizio, 
  freq_settimane) 
  VALUES
  (nextval('elem.sq_percorsi'::regclass),
  $1,
  1, NULL,
  $2, 1,
  $3, $4,
  3,
  $5, to_timestamp($6,'DD/MM/YYYY')::date,
  $7,
  NULL, 
  $8, 
  NULL,
  NULL,
  $9, 
  $10)";
  $result_sit = pg_prepare($conn, "insert_sit", $insert_sit);
  $result_sit = pg_execute($conn, "insert_sit", array($cod_percorso,$desc, $cdaog3, $turno, $_SESSION['username'], $data_att, $freq_sit, $sq_ut, $id_servizio_sit, $freq_sett)); 
}

echo  pg_last_error($conn);
# vanno popolate anche le altre tabelle del SIT (anagrafe percorsi)
echo "<br><br>Insert in elenco percorsi<br>";

$insert_elenco_percorsi= "INSERT INTO anagrafe_percorsi.elenco_percorsi (
  cod_percorso, descrizione,
  id_tipo, freq_testata,
  id_turno, durata, codice_cer,
  versione_testata, 
  data_inizio_validita, data_fine_validita, data_ultima_modifica, 
  freq_settimane) 
  VALUES
  (
    $1, $2,
    $3, $4,
    $5, $6, NULL, 
    1,
    to_timestamp($7,'DD/MM/YYYY'), to_timestamp($8,'DD/MM/YYYY'), now()
    , $9
  )";





$result_elenco = pg_prepare($conn, "insert2", $insert_elenco_percorsi);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn);

$result_elenco = pg_execute($conn, "insert2", array($cod_percorso, $desc, $tipo, $freq_sit, $turno, $durata, $data_att, $data_disatt, $freq_sett)); 
echo "<br><br> ERRORI 2: <br>";
echo  pg_last_error($conn);


echo "<br><br>Insert in elenco percorsi OLD<br>";
$insert_elenco_percorsi_old = "INSERT INTO anagrafe_percorsi.elenco_percorsi_old 
(id, id_percorso_sit,
 cod_percorso, descrizione,
 id_tipo, freq_testata,
 versione_uo, data_inizio_validita, data_fine_validita) 
 VALUES(
  nextval('anagrafe_percorsi.elenco_percorsi_old_id_seq'::regclass),
   (select id_percorso from elem.percorsi where cod_percorso = $1),
   $1, $2,
   $3, $4,
   1, to_timestamp($5,'DD/MM/YYYY'), to_timestamp($6,'DD/MM/YYYY')
   )";

$result_elenco_old = pg_prepare($conn, "insert_elenco_percorsi_old", $insert_elenco_percorsi_old);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn);
$result_elenco_old = pg_execute($conn, "insert_elenco_percorsi_old", array($cod_percorso,$desc, 
$tipo, $freq_sit,
$data_att, $data_disatt
)); 


echo  pg_result_error($result_elenco_old);
echo "<br><br> ERRORI 2: <br>";
echo  pg_last_error($conn);




echo "<br><br>Insert in elenco percorsi UT<br>";

if ($sq_ut==15){
  $vis='S';
  $rim_resp='S';
} else {
  $vis='N';
  $rim_resp='N';
}


$insert_elenco_percorsi_ut = "INSERT INTO anagrafe_percorsi.percorsi_ut (
  cod_percorso, id_ut,
  id_squadra,
  responsabile, solo_visualizzazione, rimessa,
  id_turno, durata, 
  data_attivazione, data_disattivazione,
  cdaog3) 
  VALUES($1, $2,
  $3,
  'S', $4, 'N',
  $5, $6,
  to_timestamp($7,'DD/MM/YYYY'), to_timestamp($8,'DD/MM/YYYY'),
  $9)";

$result_percorsi_ut = pg_prepare($conn, "insert_elenco_percorsi_ut", $insert_elenco_percorsi_ut);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn);
$result_percorsi_ut = pg_execute($conn, "insert_elenco_percorsi_ut", 
array($cod_percorso, $ut_uo, 
$sq_ut,
$vis,
$turno,  $durata,
$data_att, $data_disatt, 
$cdaog3
));

echo  pg_result_error($result_percorsi_ut);



if($_POST['rim']){

  $insert_elenco_percorsi_rim = "INSERT INTO anagrafe_percorsi.percorsi_ut (
    cod_percorso, id_ut,
    id_squadra,
    responsabile, solo_visualizzazione, rimessa,
    id_turno, durata, 
    data_attivazione, data_disattivazione,
    cdaog3) 
    VALUES($1, $2,
    $3,
    $4, 'N' ,'S',
    $5, $6,
    to_timestamp($7,'DD/MM/YYYY'), to_timestamp($8,'DD/MM/YYYY'),
    $9)";
  
  $result_percorsi_rim = pg_prepare($conn, "insert_elenco_percorsi_rim", $insert_elenco_percorsi_rim);
  echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn);
  $result_percorsi_rim = pg_execute($conn, "insert_elenco_percorsi_rim", 
  array($cod_percorso, $rim_uo, 
  $sq_rim,
  $rim_resp,
  $turno, $durata,
  $data_att, $data_disatt, 
  $cdaog3
  )); 

  echo  pg_result_error($result_percorsi_rim);
}

#exit();
header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=1");

?>