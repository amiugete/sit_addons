<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione servizi</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 





</head>

<body>

<?php 
require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}

$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];
?>


<div class="container">
<?php
$query_testata = "select ep.cod_percorso, 
ep.descrizione, t.cod_turno, t.id_turno, ep.durata, fo.descrizione_long, 
ep.freq_testata, fo.freq_binaria, ep.id_tipo,
at2.id_servizio_uo, at2.id_servizio_sit, 
ep.data_inizio_validita, to_char(ep.data_fine_validita, 'DD/MM/YYYY') as data_disattivazione_testata
from anagrafe_percorsi.elenco_percorsi ep
join elem.turni t on t.id_turno = ep.id_turno
join etl.frequenze_ok fo on fo.cod_frequenza = ep.freq_testata
join anagrafe_percorsi.anagrafe_tipo at2 on at2.id = ep.id_tipo 
where ep.cod_percorso = $1 and ep.versione_testata  = $2";
$result = pg_prepare($conn, "query_testata", $query_testata);
$result = pg_execute($conn, "query_testata", array($cod_percorso, $versione));  

//echo $cod_percorso . '<br>';
//echo $versione . '<br>';
?>
<h3> Testata percorso </h3>
<?php
echo '<ul>';
while($r = pg_fetch_assoc($result)) {
  echo '<li><b> Codice percorso </b>'.$r["cod_percorso"].'</li>';
  echo '<li><b> Versione percorso </b>'.$versione.'</li>';
  $desc=$r["descrizione"];
  echo '<li><b> Descrizione </b>'.$r["descrizione"].'</li>'; 
  echo '<li><b> Turno </b>'.$r["cod_turno"].'</li>';
  echo '<li><b> Durata </b>'.$r["durata"].'</li>';
  echo '<li><b> Frequenza </b>'.$r["descrizione_long"].'</li>';
  echo '<li><b> Data disattivazione </b>'.$r["data_disattivazione_testata"].'</li>';
  $freq_sit=$r["freq_testata"];
  $freq_uo=$r["freq_binaria"];
  $id_servizio_uo=$r['id_servizio_uo'];
  $id_servizio_sit= $r['id_servizio_sit'];
  $tipo= $r['id_tipo'];
  $turno=$r["id_turno"];
  $data_attivazione_testata=$r['data_inizio_validita'];
  $data_disattivazione_testata=$r['data_disattivazione_testata'];
}
echo '</ul>';

# percorso su SIT
$query_sit = 'select p.id_percorso
from elem.percorsi p 
where cod_percorso = $1 and versione = (select max(versione)
    from elem.percorsi p1 where cod_percorso = $1)';
$result_sit = pg_prepare($conn, "query_sit", $query_sit);
$result_sit = pg_execute($conn, "query_sit", array($cod_percorso));  


if ($_SESSION['test']==1) {
  $testo_tasto="Percorso su SIT (produzione NON test)";
} else {
  $testo_tasto="Percorso su SIT";
}

while($r_s = pg_fetch_assoc($result_sit)) {
  echo '<a class="btn btn-primary" target="SIT" href="'.$url_sit.'/#!/percorsi/percorso-details/?idPercorso='.$r_s["id_percorso"].'"> 
  <i class="fa-solid fa-map-location-dot"></i> '.$testo_tasto.' </a>';
}

?>
<hr>
<h3> Risorse umane e risorse tecniche </h3>
<?php
$mezzo='';
$query0="select 
u.descrizione as ut,
pu.id_squadra, 
pu.cdaog3,
pu.responsabile, 
pu.solo_visualizzazione, 
pu.rimessa, 
pu.data_attivazione, 
pu.data_disattivazione, 
u.id_ut
from anagrafe_percorsi.percorsi_ut pu 
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
join topo.ut u on u.id_ut = cmu.id_uo_sit 
where cod_percorso = $1  and data_attivazione = $2";

// RIMESSA / SEDE OPERATIVA
$query_rimessa=$query0 ." and rimessa = 'S'";
$result1 = pg_prepare($conn, "query_rimessa", $query_rimessa);
$result1 = pg_execute($conn, "query_rimessa", array($cod_percorso, $data_attivazione_testata));
echo '<ul>';
while($r1 = pg_fetch_assoc($result1)) {
  echo '<h4><li><b> Sede operativa </b>'.$r1["ut"].'</li></h4>';
  echo '<li><b> Id Squadra </b>'.$r1["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li><b> Mezzo </b>'.$r1["cdaog3"].'</li>';
  echo '<li><b> Responsabile </b>'.$r1["responsabile"].'</li>';
  echo '<li><b> Solo visualizzazione </b>'.$r1["solo_visualizzazione"].'</li>';
  echo '<li><b> Data attivazione </b>'.$r1["data_attivazione"].'</li>';
  echo '<li><b> Data disattivazione  </b>'.$r1["data_disattivazione"].'</li>';
  $rimessa=$r1["id_ut"];
  $sq_rimessa=$r1["id_squadra"];
  $mezzo=$r1["cdaog3"];

}
echo '</ul>';


// GRUPPO DI COORDINAMENTO

$query_ut=$query0 ." and pu.id_squadra!= 15 and rimessa = 'N'";
$result2 = pg_prepare($conn, "query_ut", $query_ut);
$result2 = pg_execute($conn, "query_ut", array($cod_percorso, $data_attivazione_testata));
//echo '<hr>';
if (pg_num_rows($result2) > 0){
  echo '<h4> <b>Gruppo di coordinamento</b></h4>';
} else {
  echo '<h4> <b>Nessun Gruppo di Coordinamento</b></h4>';
}
echo '<ul>';
while($r2 = pg_fetch_assoc($result2)) {
  echo '<h4><li><b> Gruppo di coordinamento</b> '.$r2["ut"].'</li></h4>';
  echo '<li><b> Id Squadra </b>'.$r2["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li><b> Mezzo </b>'.$r2["cdaog3"].'</li>';
  echo '<li><b> Responsabile </b>'.$r2["responsabile"].'</li>';
  if ($r2["responsabile"]=='S'){
    $gc=$r2["id_ut"];
    $sq_gc=$r2["id_squadra"];
    if ($mezzo==''){
      $mezzo=$r2["cdaog3"];
    }
  }
  echo '<li><b> Solo visualizzazione </b>'.$r2["solo_visualizzazione"].'</li>';
  echo '<li><b> Data attivazione </b>'.$r2["data_attivazione"].'</li>';
  echo '<li><b> Data disattivazione  </b>'.$r2["data_disattivazione"].'</li>';
}
echo '</ul>';


// ALtre UT Visualizzazione

$query_ut2=$query0 ." and pu.id_squadra= 15 and rimessa = 'N' order by responsabile desc";
$result2 = pg_prepare($conn, "query_ut2", $query_ut2);
$result2 = pg_execute($conn, "query_ut2", array($cod_percorso, $data_attivazione_testata));
//echo '<hr>';

if (pg_num_rows($result2) > 0){
  echo '<h4> <b>UT visualizzazione</b></h4>';
}

echo '<ul>';
while($r2 = pg_fetch_assoc($result2)) {
  echo '<h4><li>'.$r2["ut"].'</li></h4>';
  //echo '<li><b> Id Squadra </b>'.$r2["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li><b> Mezzo </b>'.$r2["cdaog3"].'</li>';
  echo '<li><b> Responsabile </b>'.$r2["responsabile"].'</li>';
  if ($r2["responsabile"]=='S'){
    $gc=$r2["id_ut"];
    $sq_gc=$r2["id_squadra"];
    if ($mezzo==''){
      $mezzo=$r2["cdaog3"];
    }
  }
  echo '<li><b> Solo visualizzazione </b>'.$r2["solo_visualizzazione"].'</li>';
  echo '<li><b> Data attivazione </b>'.$r2["data_attivazione"].'</li>';
  echo '<li><b> Data disattivazione  </b>'.$r2["data_disattivazione"].'</li>';
}
echo '</ul>';




?>

<form name="bilat" method="post" autocomplete="off" action="./nuova_versione.php">

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
<input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
<input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
<input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
<input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo;?>">
<input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
<input type="hidden" id="rimessa" name="rimessa" value="<?php echo $rimessa;?>">
<input type="hidden" id="sq_rimessa" name="sq_rimessa" value="<?php echo $sq_rimessa;?>">
<input type="hidden" id="gc" name="gc" value="<?php echo $gc;?>">
<input type="hidden" id="sq_gc" name="sq_gc" value="<?php echo $sq_gc;?>">
<input type="hidden" id="mezzo" name="mezzo" value="<?php echo $mezzo;?>">

<?php if ($check_edit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> <i class="fa-solid fa-arrow-up-from-bracket"></i> Nuova versione
</button>
</div>
<?php }?>
</form>

<hr>
<form name="bilat" method="post" autocomplete="off" action="./backoffice/nuova_visualizzazione.php">

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
<input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
<input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
<input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
<input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo;?>">
<input type="hidden" id="data_disattivazione" name="data_disattivazione" value="<?php echo $data_disattivazione_testata;?>">
<!--input type="hidden" id="gc" name="gc" value="<?php echo $gc;?>"-->
<input type="hidden" id="sq_ut" name="sq_ut" value="15">
<input type="hidden" id="mezzo" name="mezzo" value="<?php echo $mezzo;?>">
<input type="hidden" id="new_vers" name="new_vers" value="<?php echo $versione;?>">

<div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="ut">UT:</label> <font color="red">*</font>
                <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <option name="ut" value="">Seleziona UT in visualizzazione</option>
  <?php            
  $query3="select id_ut, descrizione 
  from topo.ut 
  where id_zona not in (5) and id_ut not in ($1)
  order by descrizione ;";
  
  $result3 = pg_prepare($conn, "query3", $query3);
  $result3 = pg_execute($conn, "query3", array($gc));
  //$result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r3 = pg_fetch_assoc($result3)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" value="<?php echo $r3['id_ut']?>" ><?php echo $r3['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>

<?php if ($check_edit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> <i class="fa-solid fa-eye"></i> Aggiungi in visualizzazione
</button>
</div>
<?php }?>
</form>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>