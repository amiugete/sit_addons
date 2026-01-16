<?php
session_start();
#require('../validate_input.php');


$res_ok=0;


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


// TURNO
$turno = intval($_POST['turno']);
//echo $turno."<br>";

$query3="SELECT aft.CODICE_TURNO, at2.DURATA
FROM ANAGR_TURNI at2
JOIN ANAGR_FASCIA_TURNO aft ON aft.FASCIA_TURNO = at2.FASCIA_TURNO 
WHERE DTA_DISATTIVAZIONE > SYSDATE AND at2.ID_TURNO = :p1";


$result3 = oci_parse($oraconn, $query3);
oci_bind_by_name($result3, ':p1', $turno);
oci_execute($result3);
while($r3 = oci_fetch_assoc($result3)) { 
  $id_percorso_turno=$r3['CODICE_TURNO'];
  $durata=$r3['DURATA'];
}
oci_free_statement($result3);


echo $durata;



$desc = $_POST['desc'];
//echo $desc."<br>";



$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";

$freq_uo = $_POST['freq_uo'];
$freq_sit = floor($_POST['freq_sit']);
$freq_sett = $_POST['freq_sett'];

$id_servizio_uo = intval($_POST['id_servizio_uo']);
if($_POST['id_servizio_sit']){
  $id_servizio_sit = intval($_POST['id_servizio_sit']);
}
$tipo = $_POST['tipo'];

//echo $tipo."<br>";
//echo $id_servizio_uo."<br>";
//echo $id_servizio_sit."<br>";



//$durata = intval($_POST['durata']);
//echo $durata."<br>";

$new_vers = intval($_POST['new_vers']);
//echo $new_vers."<br>";



$data_disatt = $_POST['data_disattivazione'];

#mezzo


// bisogna calcolare la data di attivazione a domani
//$tomorrow = new DateTime('tomorrow');
//$data_att=$tomorrow->format('d/m/Y');

// modificato nella pagina dettaglio
$data_att = $_POST['data_attivazione'];


//echo "data_att: ".$data_att."<br>";





//echo "data_disatt: ".$data_disatt."<br>";



$id_ut_sit = $_POST['ut'];


exit();












// insert uo di questi dati come per la creazione con data attivazione = domani e data disattivazione = a quella dei percorsi esistenti (metti nella schermata precedente)



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
    to_date(:p4,'YYYY-MM-DD'), to_date(:p5,'DD/MM/YYYY'),
    :p6, NULL, 
    :p7,
    :p8, 
    :p9,
    1,
    :p10, 
    :p11)";







$ut_sit = intval($_POST['ut']);
$sq_ut = intval($_POST['sq_ut']);

# cerco id ut UO
$query2="select id_uo 
from anagrafe_percorsi.cons_mapping_uo cmu 
where id_uo_sit = $1;";
$result2 = pg_prepare($conn, "query2", $query2);
$result2 = pg_execute($conn, "query2", array($ut_sit));  
#echo $query1;    
while($r2 = pg_fetch_assoc($result2)) { 
  $ut_uo=intval($r2['id_uo']);
}
//echo $sq_ut."<br>";
//echo $ut_sit."<br>";
//echo $ut_uo."<br>";
//echo $data_att."<br>";
//echo $data_disatt."<br>";


//echo $insert_uo."<br>";
//exit();


# INSERT UO 
$result_uo2 = oci_parse($oraconn, $insert_uo);
# passo i parametri
oci_bind_by_name($result_uo2, ':p1', $ut_uo);
oci_bind_by_name($result_uo2, ':p2', $cod_percorso);
oci_bind_by_name($result_uo2, ':p3', $turno);
oci_bind_by_name($result_uo2, ':p4', $data_att);
oci_bind_by_name($result_uo2, ':p5', $data_disatt);
oci_bind_by_name($result_uo2, ':p6', $durata);
//oci_bind_by_name($result_uo2, ':p7', $automezzo);
oci_bind_by_name($result_uo2, ':p7', $desc);
oci_bind_by_name($result_uo2, ':p8', $id_servizio_uo);
oci_bind_by_name($result_uo2, ':p9', $sq_ut);
oci_bind_by_name($result_uo2, ':p10', $freq_uo);
oci_bind_by_name($result_uo2, ':p11', $freq_sett);


# commit
$ris=oci_execute($result_uo2);

if (!$ris) {
  $res_ok= $res_ok+1;
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

//echo "<br> sono arrivato qua";
oci_free_statement($result_uo2);



#exit();
// DA CONTROLLARE!!!




#######################################################################
# qua dovrebbe essere ok com le date modificate nella pag precedente

# verificare le altre UT in visualizzazione
#######################################################################


//echo "<br><br>Insert in elenco percorsi UT<br>";

if ($sq_ut==15){
  $vis='S';
} else {
  $vis='N';
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
  'N', $4, 'N',
  $5, $6,
  to_timestamp($7,'YYYY-MM-DD'), to_timestamp($8,'DD/MM/YYYY'),
  NULL)";

$result_percorsi_ut = pg_prepare($conn, "insert_elenco_percorsi_ut", $insert_elenco_percorsi_ut);
//echo "<br><br> ERRORI 1: <br>";
if (!pg_last_error($conn)){
  #$res_ok=0;
} else {
  pg_last_error($conn);
  $res_ok= $res_ok+1;
}



$result_percorsi_ut = pg_execute($conn, "insert_elenco_percorsi_ut", 
array($cod_percorso, $ut_uo, 
$sq_ut,
$vis,
$turno,  $durata,
$data_att, $data_disatt
));

if (!pg_last_error($conn)){
  #$res_ok=0;
} else {
  pg_last_error($conn);
  $res_ok= $res_ok+1;
}


if ($res_ok==0){
  echo '<font color="green"> Visualizzazione aggiunta, chiudi e apri il form per visualizzare i risultati!</font>';
} else {
  echo '<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}
#exit();
//header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$new_vers."");

?>