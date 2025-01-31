<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}



$res_ok=0;

// TURNO
$turno = intval($_POST['turno']);
echo $turno."<br>";

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


echo $durata."<br>";;



$desc = $_POST['desc'];
echo $desc."<br>";

$cod_percorso = $_POST['id_percorso'];
echo $cod_percorso."<br>";

require('decodifica_frequenza.php');


/*echo $_POST['freq'];
$check_freq=0;

/if ($_POST['freq']=='0'){
  $check_freq=1;
} else if ($_POST['freq']){
  $check_freq=1;
}
*/

$freq_sit_old=$_POST['freq_sit'];
$check_freq=1;


if ($check_freq==1){ 
  // FREQUENZA
  echo "Modifico la frequenza";
  $freq = $_POST['freq'];

  $query4="select cod_frequenza as freq_sit,
  freq_binaria as freq_uo,
  descrizione_long 
  from etl.frequenze_ok fo 
  where cod_frequenza=$1::bit(12)::int;";
  $result4 = pg_prepare($conn, "query4", $query4);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result4 = pg_execute($conn, "query4", array($frequenza_binaria));  
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  //echo $query1;    
  while($r4 = pg_fetch_assoc($result4)) { 
    $freq_sit=$r4['freq_sit'];
    $freq_uo=$r4['freq_uo'];
    $descrizione_long = $r4['descrizione_long'];
  }
} else {
  echo "NON Modifico la frequenza";
  $freq_uo = $_POST['freq_uo'];
  $freq_sit = floor($_POST['freq_sit']);
}




echo $freq_uo."<br>";
echo $freq_sit."<br>";
echo $freq_sit_old."<br>";


$freq_sett = $_POST['freq_sett'];
echo $freq_sett."<br>";

// devo verificare se è cambiata o meno la frequenza
$cambio_frequenza_sit=0;
if ($freq_sit!=$freq_sit_old){
  $cambio_frequenza_sit=1;
}

echo $cambio_frequenza_sit.'<br>';

// devo verificare se è cambiata o meno la frequenza
//exit();

$id_servizio_uo = intval($_POST['id_servizio_uo']);
if($_POST['id_servizio_sit']){
  $id_servizio_sit = intval($_POST['id_servizio_sit']);
}
$tipo = $_POST['tipo'];

echo $tipo."<br>";
echo $id_servizio_uo."<br>";
echo $id_servizio_sit."<br>";



//$durata = intval($_POST['durata']);
//echo $durata."<br>";

$new_vers = intval($_POST['new_vers']);
echo $new_vers."<br>";


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
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
$result0 = pg_execute($conn, "query0", array($cdaog3)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
} 
//echo $query1;    
while($r0 = pg_fetch_assoc($result0)) { 
  $automezzo=$r0['categoria'];
}
echo $automezzo."<br>";


echo $data_att."<br>";

echo $data_disatt."<br>";






//exit();



// update data_disattivazione = domani di quanto attivo fino ad ora

$update_uo= "UPDATE ANAGR_SER_PER_UO aspu
SET DTA_DISATTIVAZIONE = to_date(:c0, 'DD/MM/YYYY') 
WHERE ID_PERCORSO = :c1 
AND DTA_DISATTIVAZIONE > SYSDATE";

$result_uo0 = oci_parse($oraconn, $update_uo);
# passo i parametri
oci_bind_by_name($result_uo0, ':c0', $data_att);
oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
oci_execute($result_uo0);
oci_free_statement($result_uo0);



$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET data_fine_validita= To_DATE($1, 'DD/MM/YYYY'), data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}


$result_usit1 = pg_execute($conn, "update_sit1", array($data_att, $cod_percorso)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>";


$update_sit2="UPDATE anagrafe_percorsi.elenco_percorsi_old epo
SET data_fine_validita= To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_fine_validita > now()";


$result_usit2 = pg_prepare($conn, "update_sit2", $update_sit2);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

$result_usit2 = pg_execute($conn, "update_sit2", array($data_att, $cod_percorso)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>";


$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET data_disattivazione = To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_disattivazione > now()";

$result_usit3 = pg_prepare($conn, "update_sit3", $update_sit3);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
$result_usit3 = pg_execute($conn, "update_sit3", array($data_att, $cod_percorso)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

echo "<br><br>Update anagrafe_percorsi.percorsi_ut<br>";


$update_sit4="UPDATE anagrafe_percorsi.date_percorsi_sit_uo dps
SET data_fine_validita = To_DATE($1, 'DD/MM/YYYY')
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit4 = pg_prepare($conn, "update_sit4", $update_sit4);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
$result_usit4 = pg_execute($conn, "update_sit4", array($data_att, $cod_percorso)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

echo "<br><br>Update anagrafe_percorsi.date_percorsi_sit_uo<br>";






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
    if (pg_last_error($conn)){
      echo pg_last_error($conn).'<br>';
      $res_ok=$res_ok+1;
    }

    $result1 = pg_execute($conn, "query1", array($rim_sit));  
    if (pg_last_error($conn)){
      echo pg_last_error($conn).'<br>';
      $res_ok=$res_ok+1;
    }
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
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

$result2 = pg_execute($conn, "query2", array($ut_sit)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
#echo $query1;    
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


# verificare se ci sono altre UT in visualizzazione sulla UO e nel caso creare nuova versione


$query_perc_vis="SELECT ID_UO 
FROM ANAGR_SER_PER_UO aspu 
WHERE ID_PERCORSO = :c1 AND DTA_DISATTIVAZIONE > SYSDATE 
AND ID_UO NOT IN (:c2, :c3)";

$result_uo_vis = oci_parse($oraconn, $query_perc_vis);
# passo i parametri
oci_bind_by_name($result_uo_vis, ':c1', $codice_percorso);
oci_bind_by_name($result_uo_vis, ':c2', $ut_uo);
oci_bind_by_name($result_uo_vis, ':c3', $rim_uo);
oci_execute($result_uo_vis);

while($ruv = oci_fetch_assoc($result_uo_vis)) { 
  $ut_vis= $ruv['ID_UO'];
  # INSERT UO  
  $result_uo_vis1 = oci_parse($oraconn, $insert_uo);
  $squadra_visualizzazione=15;
  # passo i parametri
  oci_bind_by_name($result_uo_vis1, ':p1', $ut_vis);
  oci_bind_by_name($result_uo_vis1, ':p2', $cod_percorso);
  oci_bind_by_name($result_uo_vis1, ':p3', $turno);
  oci_bind_by_name($result_uo_vis1, ':p4', $data_att);
  oci_bind_by_name($result_uo_vis1, ':p5', $data_disatt);
  oci_bind_by_name($result_uo_vis1, ':p6', $durata);
  oci_bind_by_name($result_uo_vis1, ':p7', $automezzo);
  oci_bind_by_name($result_uo_vis1, ':p8', $desc);
  oci_bind_by_name($result_uo_vis1, ':p9', $id_servizio_uo);
  oci_bind_by_name($result_uo_vis1, ':p10', $squadra_visualizzazione);
  oci_bind_by_name($result_uo_vis1, ':p11', $freq_uo);
  oci_bind_by_name($result_uo_vis1, ':p12', $freq_sett);
  # commit
  $ris=oci_execute($result_uo_vis1);
  oci_free_statement($result_uo_vis1);
}
oci_free_statement($result_uo_vis);


#########################################################################################################################
# questo per ora non va fatto perchè non devo generare nuovo percorso ma solo cambiare turno / automezzo / squadra (comanda la UO)
#########################################################################################################################


if ($id_servizio_sit!=0 AND $cambio_frequenza_sit ==1 ){
  

  // recupero id percorso da distattivare e se in esercizio o stagionale

  $select_sit="SELECT id_categoria_uso, id_percorso FROM elem.percorsi  
  WHERE cod_percorso=$1 and id_categoria_uso in (3,6)";
  $result_sit0 = pg_prepare($conn, "select_sit", $select_sit);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }

  $result_sit0 = pg_execute($conn, "select_sit", array($cod_percorso));
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }

  while($rs = pg_fetch_assoc($result_sit0)) { 
    $id_categoria_uso=intval($rs['id_categoria_uso']);
    $id_percorso_old=intval($rs['id_percorso']);
  }
  

  #UPDATE 
  //disattivo il percorso su SIT
  $update_sit="UPDATE elem.percorsi set id_categoria_uso=4, 
  data_dismissione= to_date($1, 'DD/MM/YYYY')
  WHERE id_percorso = $2";
  $result_sit1 = pg_prepare($conn, "update_sit", $update_sit);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result_sit1 = pg_execute($conn, "update_sit", array($data_att, $id_percorso_old));
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }



  # INSERT SIT
  # creo nuovo percorso su SIT
  $insert_sit="INSERT INTO elem.percorsi 
  (id_percorso, cod_percorso, 
  versione, id_ut_resp,
  descrizione, ente_effettuante,
  famiglia_mezzo, id_turno, 
  attivatore, frequenza,
  stagionalita, id_squadra, 
  ddmm_switch_on, ddmm_switch_off, 
  id_servizio, freq_settimane,
  id_categoria_uso, data_attivazione) 
  VALUES (
  nextval('elem.sq_percorsi'::regclass), $1,
  (select max(versione)+1 from elem.percorsi where cod_percorso=$2), NULL,
  $3, 1,
  $4, $5,
  $6, $7,
  NULL, $8, 
  NULL, NULL,
  $9, $10,
  $11, to_date($12, 'DD/MM/YYYY')
  ) returning id_percorso";
  $result_sit = pg_prepare($conn, "insert_sit", $insert_sit);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result_sit = pg_execute($conn, "insert_sit", array($cod_percorso,$cod_percorso, $desc, $cdaog3, $turno, $_SESSION['username'], $freq_sit, $sq_ut, $id_servizio_sit, $freq_sett, $id_categoria_uso, $data_att)); 
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  // tiro fuori nuovo id_percorso
  $new_id=0;
  $arr = pg_fetch_array($result_sit, 0, PGSQL_NUM);
  $new_id=intval($arr[0]);


  // tappe sul nuovo percorso
  // 'F' applico la frequenza a tutte le tappe del nuovo percorso
  // 'I' ANCORA DA IMPLEMENTARE !!!!!!!!!
  $clona_tappe_nuova_freq="SELECT elem.clona_percorso_new_freq($1,$2,$3,'F')";


  $result_sit2 = pg_prepare($conn, "clona_tappe_nuova_freq", $clona_tappe_nuova_freq);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }

  $result_sit2 = pg_execute($conn, "clona_tappe_nuova_freq", array($id_percorso_old, $new_id, $freq_sit)); 
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }


  $description_log='Nuova versione percorso. Cambiata frequenza da '.$freq_sit_old. ' a '. $freq_sit; 
  // salvo il log
  $insert_history="INSERT INTO util.sys_history 
  (\"type\", \"action\", 
  description, 
  datetime,  id_user, 
  id_percorso) 
   VALUES(
   'PERCORSO', 'UPDATE',
   $1, 
   CURRENT_TIMESTAMP, 
   (select id_user from util.sys_users su where \"name\" ilike $2), 
   $3);";

$result_sit3 = pg_prepare($conn, "insert_history", $insert_history);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

$result_sit3 = pg_execute($conn, "insert_history", array($description_log, $_SESSION['username'], $new_id)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

  // aggiorno contestualmente la tabella anagrafe_percorsi.date_percorsi_sit_uo

  // NOn serve

  /*
  #UPDATE 
  $update_sit1="UPDATE anagrafe_percorsi.date_percorsi_sit_uo set data_fine_validita = to_date($1, 'DD/MM/YYYY')
  WHERE id_percorso _sit = $2 and data_fine_validita > sysdate";
  $result_sit2 = pg_prepare($conn, "update_sit1", $update_sit1);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result_sit2 = pg_execute($conn, "update_sit1", array($data_att, $id_percorso_old));
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }

  # INSERT
  $insert_sit1="INSERT INTO anagrafe_percorsi.date_percorsi_sit_uo 
(id_percorso_sit, cod_percorso,
 data_inizio_validita, 
 data_fine_validita) 
 VALUES (
 $1, $2,
 to_date($3, 'DD/MM/YYYY'),
 to_date($4, 'DD/MM/YYYY')
 )";
  $result_sit3 = pg_prepare($conn, "insert_sit1", $insert_sit1);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result_sit3 = pg_execute($conn, "insert_sit1", array($new_id, $cod_percorso, $data_att, $data_disatt));
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
*/
}



# vanno popolate anche le altre tabelle del SIT (anagrafe percorsi)
echo "<br><br>Insert in elenco percorsi<br>";


#############################################
# qua va cambiata la versione
#############################################

$insert_elenco_percorsi= "INSERT INTO anagrafe_percorsi.elenco_percorsi (
  cod_percorso, descrizione,
  id_tipo, freq_testata, 
  id_turno, durata, codice_cer,
  versione_testata, 
  data_inizio_validita, data_fine_validita, data_ultima_modifica, freq_settimane ) 
  VALUES
  (
    $1, $2,
    $3, $4,
    $5, $6, NULL, 
    $7,
    to_timestamp($8,'DD/MM/YYYY'), to_timestamp($9,'DD/MM/YYYY'), now()
    ,$10
  )";





$result_elenco = pg_prepare($conn, "insert2", $insert_elenco_percorsi);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}

$result_elenco = pg_execute($conn, "insert2", array($cod_percorso, $desc, $tipo, $freq_sit, $turno, $durata, $new_vers, $data_att, $data_disatt, $freq_sett)); 
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}


#############################################
# qua va cambiata la versione
#############################################


echo "<br><br>Insert in elenco percorsi OLD<br>";
$insert_elenco_percorsi_old = "INSERT INTO anagrafe_percorsi.elenco_percorsi_old 
(id, id_percorso_sit,
 cod_percorso, descrizione,
 id_tipo, freq_testata,
 versione_uo, data_inizio_validita, data_fine_validita) 
 VALUES(
  nextval('anagrafe_percorsi.elenco_percorsi_old_id_seq'::regclass),
   (select max(id_percorso) from elem.percorsi where cod_percorso = $1),
   $1, $2,
   $3, $4,
   $5, to_timestamp($6,'DD/MM/YYYY'), to_timestamp($7,'DD/MM/YYYY')
   )";

$result_elenco_old = pg_prepare($conn, "insert_elenco_percorsi_old", $insert_elenco_percorsi_old);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}
$result_elenco_old = pg_execute($conn, "insert_elenco_percorsi_old", array($cod_percorso,$desc, 
$tipo, $freq_sit, $new_vers,
$data_att, $data_disatt
)); 

if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}



  echo "<br><br>Insert in date_percorsi_sit_uo<br>";
  $insert_date_percorsi_sit_uo = "INSERT INTO anagrafe_percorsi.date_percorsi_sit_uo 
  (id_percorso_sit,
  cod_percorso, versioni_uo, data_inizio_validita, data_fine_validita) 
  VALUES(
    (select max(id_percorso) from elem.percorsi where cod_percorso = $1),
    $1, 1,
    to_timestamp($2,'DD/MM/YYYY'), to_timestamp($3,'DD/MM/YYYY')
    )";

  $result_date_percorsi_sit_uo = pg_prepare($conn, "insert_date_percorsi_sit_uo", $insert_date_percorsi_sit_uo);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  $result_date_percorsi_sit_uo = pg_execute($conn, "insert_date_percorsi_sit_uo", array($cod_percorso, $data_att, $data_disatt)); 


  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
}





#######################################################################
# qua dovrebbe essere ok com le date modificate nella pag precedente

# verificare le altre UT in visualizzazione
#######################################################################


echo "<br><br>Insert in elenco percorsi UT<br>";

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
  'S', $4, 'N',
  $5, $6,
  to_timestamp($7,'DD/MM/YYYY'), to_timestamp($8,'DD/MM/YYYY'),
  $9)";

$result_percorsi_ut = pg_prepare($conn, "insert_elenco_percorsi_ut", $insert_elenco_percorsi_ut);
if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}


$result_percorsi_ut = pg_execute($conn, "insert_elenco_percorsi_ut", 
array($cod_percorso, $ut_uo, 
$sq_ut,
$vis,
$turno,  $durata,
$data_att, $data_disatt, 
$cdaog3
));

if (pg_last_error($conn)){
  echo pg_last_error($conn).'<br>';
  $res_ok=$res_ok+1;
}



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
    'N', 'N', 'S',
    $4, $5,
    to_timestamp($6,'DD/MM/YYYY'), to_timestamp($7,'DD/MM/YYYY'),
    $8)";
  
  $result_percorsi_rim = pg_prepare($conn, "insert_elenco_percorsi_rim", $insert_elenco_percorsi_rim);
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }

  
  $result_percorsi_rim = pg_execute($conn, "insert_elenco_percorsi_rim", 
  array($cod_percorso, $rim_uo, 
  $sq_rim,
  $turno, $durata,
  $data_att, $data_disatt, 
  $cdaog3
  )); 
  if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
  }
  echo  pg_result_error($result_percorsi_rim);
}

if ($res_ok ==0){
  #exit();
  header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$new_vers."");
}
?>