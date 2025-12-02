<?php
session_start();
#require('../validate_input.php');

if ($_SESSION['test']==1) {
    echo "CONNESSIONE TEST<br>";
    $checkTest=1;
    require_once ('../conn_test.php');
} else {
    echo "CONNESSIONE ESERCIZIO<br>";
    $checkTest=0;
    require_once ('../conn.php');
}
//echo "OK";

if ($_POST['rim']==$_POST['ut']){
  echo "ATTENZIONE. Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa. Torna indietro e corregg";
  exit();
}

$turno = intval($_POST['turno']);
echo $turno."<br>";

$check_refday = intval($_POST['refday']);
echo $check_refday."<br>";

$desc = $_POST['desc'];
echo $desc."<br>";

$cod_percorso = $_POST['id_percorso'];
echo $cod_percorso."<br>";

$freq_uo = $_POST['freq_uo'];
$freq_sit = floor($_POST['freq_sit']);

echo $freq_uo."<br>";
echo $freq_sit."<br>";

if ($_POST['freq_sett']){
  $freq_sett = $_POST['freq_sett'];
} else {
  $freq_sett='T';
}


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



if ($_POST['check_EKO']){
  $check_EKO = $_POST['check_EKO'];
} else {
  $check_EKO = 'f';
}
echo "check_EKO:".$check_EKO."<br>";
#exit();


$durata = intval($_POST['durata']);

echo $durata."<br>";


$data_att = $_POST['data_att'];
$data_disatt = $_POST['data_disatt'];


$stag =  $_POST['stag'];
$switchON = $_POST['switchon'];
$switchOFF = $_POST['switchoff'];
if ($stag==''){
  $stag = NULL;
  $switchON = null;
  $switchOFF = null;
  $data_disatt_sit = NULL;
  $id_uso = 3;
}else{
  $data_disatt_sit = $_POST['data_disatt'];
  $data_att_dt = DateTime::createFromFormat('d/m/Y', $data_att);
  $data_att_dt->setTime(0, 0, 0);

  $domani = new DateTime('tomorrow');
  $domani->setTime(0, 0, 0);

  if($data_att_dt == $domani){
    $id_uso = 3;
    //echo 'data attivazione ('.$data_att.') = domani</br>';
  }else{
    $id_uso = 6;
    //echo 'data attivazione ('.$data_att.') != domani</br>';
  }
}

echo $stag."<br>";
echo $switchON."<br>";
echo $switchOFF."<br>";

#mezzo

$cdaog3 = $_POST['cdaog3'];
echo $cdaog3."<br>";


$query0="select cdaog3,
categoria  
from elem.automezzi a 
where cdaog3= $1"; 
$result0 = pg_prepare($conn_sit, "query0", $query0);
$result0 = pg_execute($conn_sit, "query0", array($cdaog3));  
//echo $query1;    
while($r0 = pg_fetch_assoc($result0)) { 
  $automezzo=$r0['categoria'];
}
echo $automezzo."<br>";

if (!empty($_POST['destinazioni'])) {
  $destinazioni= $_POST['destinazioni'];
} else {
    $destinazioni = [];
}
echo "n. destinazioni: ".count($destinazioni)."<br>";

if (!empty($_POST['percentuali'])) {
  $comuni_percent = $_POST['percentuali'];}
else {
    $comuni_percent = [];
}

foreach($_POST['percentuali'] as $key => $value){
  echo "Comune: ".$key." - Percentuale: ".$value."<br>";
}

#exit();
$ut_sit = intval($_POST['ut']);
$sq_ut = intval($_POST['sq_ut']);

#exit();


# cerco id ut UO
$query2="select id_uo 
from anagrafe_percorsi.cons_mapping_uo cmu 
where id_uo_sit = $1;";
$result2 = pg_prepare($conn_sit, "query2", $query2);
$result2 = pg_execute($conn_sit, "query2", array($ut_sit));  
//echo $query1;    
while($r2 = pg_fetch_assoc($result2)) { 
  $ut_uo=intval($r2['id_uo']);
}

if($_POST['rim']){
      $rim_sit = $_POST['rim'];
      $sq_rim = $_POST['sq_rim'];
      
      #cerco id rimessa UO
      $query1="select id_uo 
      from anagrafe_percorsi.cons_mapping_uo cmu 
      where id_uo_sit = $1;";
      $result1 = pg_prepare($conn_sit, "query1", $query1);
      $result1 = pg_execute($conn_sit, "query1", array($rim_sit));  
      //echo $query1;    
      while($r1 = pg_fetch_assoc($result1)) { 
        $rim_uo=$r1['id_uo'];
      }
}
###################### INIZIO INSERT UO #########################

if ($checkTest == 0){
  echo "faccio insert su UO <br>";


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
      :p12) returning ID_SER_PER_UO INTO :id_ser_uo";



  if($_POST['rim']){
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

  }// fine if rimessa

  
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
  oci_bind_by_name($result_uo2, ":id_ser_uo", $id_ser_uo, 32);


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

  if(count($comuni_percent)>0){
    $insert_uo_comuni = "INSERT INTO UNIOPE.ANAGR_SER_PER_UO_COMUNI 
        (ID_SER_PER_UO, ID_COMUNE, PERCENTUALE) 
        VALUES
        (:p1, :p2, :p3)";

    foreach($comuni_percent as $id_comune => $percentuale){
      $result_uo_com = oci_parse($oraconn, $insert_uo_comuni);
      # passo i parametri
      oci_bind_by_name($result_uo_com, ':p1', $id_ser_uo);
      oci_bind_by_name($result_uo_com, ':p2', $id_comune);
      oci_bind_by_name($result_uo_com, ':p3', $percentuale);
      $risuocom=oci_execute($result_uo_com);

      if (!$risuocom) {
        echo "<br>ci sono errori<br>";
        $e = oci_error($result_uo_com);  // For oci_execute errors pass the statement handle
        echo $e;
        echo $e['message'];
        echo htmlentities($e['message']);
        echo "\n<pre>\n";
        echo htmlentities($e['sqltext']);
        echo "\n%".($e['offset']+1)."s", "^";
        echo  "\n</pre>\n";
      }

      echo "<br> sono arrivato qua a inserire i comuni";
      oci_free_statement($result_uo_com);
    }
  }// fine if comuni percentuali

  if(count($destinazioni)>0){
      $insert_uo_destinazioni = "INSERT INTO UNIOPE.ANAGR_SER_PER_UO_DEST 
        (ID_SER_PER_UO, ID_DESTINAZIONE) 
        VALUES
        (:p1, :p2)";

      foreach($destinazioni as $d){
        $result_uo_dest = oci_parse($oraconn, $insert_uo_destinazioni);
        # passo i parametri
        oci_bind_by_name($result_uo_dest, ':p1', $id_ser_uo);
        oci_bind_by_name($result_uo_dest, ':p2', $d);
        $risuo=oci_execute($result_uo_dest);

        if (!$risuo) {
          echo "<br>ci sono errori<br>";
          $e = oci_error($result_uo_dest);  // For oci_execute errors pass the statement handle
          echo $e;
          echo $e['message'];
          echo htmlentities($e['message']);
          echo "\n<pre>\n";
          echo htmlentities($e['sqltext']);
          echo "\n%".($e['offset']+1)."s", "^";
          echo  "\n</pre>\n";
        }

        echo "<br> sono arrivato qua a inserire le destinazioni";
        oci_free_statement($result_uo_dest);
      }
    }// fine if destinazioni
}else{
  echo "NON faccio insert su UO in quanto sono in TEST <br>";
}
#exit();
###################### FINE INSERT UO #########################


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
  data_dismissione,
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
  $5,
  $6, to_timestamp($7,'DD/MM/YYYY')::date,";
  if(!is_null($stag)){
    $insert_sit .="to_timestamp($8,'DD/MM/YYYY')::date,";
  }else{
    $insert_sit .="$8,";
  }
  $insert_sit .="$9,
  $10, 
  $11, 
  $12,
  $13,
  $14, 
  $15)";
  $result_sit = pg_prepare($conn_sit, "insert_sit", $insert_sit);
  $result_sit = pg_execute($conn_sit, "insert_sit", array($cod_percorso,$desc, $cdaog3, $turno, $id_uso, $_SESSION['username'], $data_att, $data_disatt_sit, $freq_sit, $stag, $sq_ut, $switchON, $switchOFF, $id_servizio_sit, $freq_sett)); 
}// fine insert sit

echo  pg_last_error($conn_sit);
# vanno popolate anche le altre tabelle del SIT (anagrafe percorsi)
echo "<br><br>Insert in elenco percorsi<br>";

$insert_elenco_percorsi= "INSERT INTO anagrafe_percorsi.elenco_percorsi (
  cod_percorso, descrizione,
  id_tipo, freq_testata,
  id_turno, durata, codice_cer,
  versione_testata, 
  data_inizio_validita, data_fine_validita, data_ultima_modifica, 
  freq_settimane, ekovision, stagionalita, ddmm_switch_on, ddmm_switch_off, giorno_competenza) 
  VALUES
  (
    $1, $2,
    $3, $4,
    $5, $6, NULL, 
    1,
    to_timestamp($7,'DD/MM/YYYY'), to_timestamp($8,'DD/MM/YYYY'), now()
    , $9, $10, $11, $12, $13, $14
  )";





$result_elenco = pg_prepare($conn_sit, "insert2", $insert_elenco_percorsi);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn_sit);

$result_elenco = pg_execute($conn_sit, "insert2", array($cod_percorso, $desc, $tipo, $freq_sit, $turno, $durata, $data_att, $data_disatt, $freq_sett, $check_EKO, $stag, $switchON, $switchOFF, $check_refday)); 
echo "<br><br> ERRORI 2: <br>";
echo  pg_last_error($conn_sit);


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

$result_elenco_old = pg_prepare($conn_sit, "insert_elenco_percorsi_old", $insert_elenco_percorsi_old);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn_sit);
$result_elenco_old = pg_execute($conn_sit, "insert_elenco_percorsi_old", array($cod_percorso,$desc, 
$tipo, $freq_sit,
$data_att, $data_disatt
)); 


echo  pg_result_error($result_elenco_old);
echo "<br><br> ERRORI 2: <br>";
echo  pg_last_error($conn_sit);




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

$result_percorsi_ut = pg_prepare($conn_sit, "insert_elenco_percorsi_ut", $insert_elenco_percorsi_ut);
echo "<br><br> ERRORI 1: <br>";
echo  pg_last_error($conn_sit);
$result_percorsi_ut = pg_execute($conn_sit, "insert_elenco_percorsi_ut", 
array($cod_percorso, $ut_uo, 
$sq_ut,
$vis,
$turno,  $durata,
$data_att, $data_disatt, 
$cdaog3
));

echo  pg_result_error($result_percorsi_ut);

echo "<br><br> CIAO<br>";

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
  
  $result_percorsi_rim = pg_prepare($conn_sit, "insert_elenco_percorsi_rim", $insert_elenco_percorsi_rim);
  echo "<br><br> ERRORI 1: <br>";
  echo pg_last_error($conn_sit);
  $result_percorsi_rim = pg_execute($conn_sit, "insert_elenco_percorsi_rim", 
  array($cod_percorso, $rim_uo, 
  $sq_rim,
  $rim_resp,
  $turno, $durata,
  $data_att, $data_disatt, 
  $cdaog3
  )); 

  echo  pg_result_error($result_percorsi_rim);
}

echo "<br><br>destinazioni: ".count($destinazioni)."<br>";
if(count($destinazioni)>0){
  echo "<br><br>Insert in percorsi_destinazione<br>";
  $dest = explode(",",$destinazioni);
  $insert_destinazioni = "INSERT INTO anagrafe_percorsi.percorsi_destinazione 
    (cod_percorso, versione, id_destinazione) 
    VALUES
    ($1, 1, $2)";
  foreach($dest as $d){
    echo "<br><br> insert di destinazione: ".$d." <br>";
    $result_destinazioni = pg_prepare($conn_sit, "insert_destinazioni_".$d, $insert_destinazioni);
    echo "<br><br> ERRORI DESTINAZIONI PREP: <br>";
    echo  pg_last_error($conn_sit);
    $result_destinazioni = pg_execute($conn_sit, "insert_destinazioni_".$d, array($cod_percorso, $d)); 
    echo "<br><br> ERRORI DESTINAZIONI EXEC: <br>";
    echo  pg_last_error($conn_sit);
    echo  pg_result_error($result_destinazioni);
  }
}

if(count($comuni_percent)>0){
  echo "<br><br>Insert in percorsi_comuni_percentuali<br>";
  $insert_comuni_percentuali = "INSERT INTO anagrafe_percorsi.percorsi_comuni
  (cod_percorso, versione, id_comune, competenza) 
    VALUES
    ($1, 1, $2, $3)";
  foreach($comuni_percent as $id_comune => $percentuale){
    $result_comuni_percentuali = pg_prepare($conn_sit, "insert_comuni_percentuali_".$id_comune, $insert_comuni_percentuali);
    echo "<br><br> ERRORI COMUNI PERCENTUALI PREP: <br>";
    echo  pg_last_error($conn_sit);
    $result_comuni_percentuali = pg_execute($conn_sit, "insert_comuni_percentuali_".$id_comune, array($cod_percorso, $id_comune, $percentuale)); 
    echo "<br><br> ERRORI COMUNI PERCENTUALI EXEC: <br>";
    echo  pg_last_error($conn_sit);
    echo  pg_result_error($result_comuni_percentuali);
  }
}

#exit();
header("location: ../percorsi.php?cp=".$cod_percorso."&v=1");
#header("location: ../percorsi.php?cp=0101000301&v=1");
#header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=1");

?>