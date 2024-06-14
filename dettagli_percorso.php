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


// verifico se c'è già una versione successiva
$check_versione_successiva=0;
$query_check_versione="select ep.cod_percorso from anagrafe_percorsi.elenco_percorsi ep
where ep.cod_percorso = $1 and ep.versione_testata > $2";
$result0 = pg_prepare($conn, "query_check_versione", $query_check_versione);
$result0 = pg_execute($conn, "query_check_versione", array($cod_percorso, intval($versione)));
while($r0 = pg_fetch_assoc($result0)) {
  $check_versione_successiva=1;
}

$check_in_attivazione=0;
?>


<div class="container">
<?php
$query_testata = "select ep.cod_percorso, 
ep.descrizione, t.cod_turno, t.id_turno, ep.durata, fo.descrizione_long, 
ep.freq_testata, fo.freq_binaria, ep.id_tipo,
at2.id_servizio_uo, at2.id_servizio_sit, 
to_char(ep.data_inizio_validita, 'DD/MM/YYYY') as data_inizio_print,
ep.data_inizio_validita, to_char(ep.data_fine_validita, 'DD/MM/YYYY') as data_disattivazione_testata,
case 
when ep.data_fine_validita < now() then 1
else 0
end flg_disattivo, 
case 
when ep.data_inizio_validita > now()::date then 1
else 0
end flg_in_attivazione,
to_char(ep.data_ultima_modifica, 'DD/MM/YYYY HH:MI') as data_ultima_modifica
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
<h3> Testata percorso  
<a class="btn btn-info"  href="./percorsi.php"> <i class="fa-solid fa-table-list"></i> Torna a elenco percorsi </a>
<?php if (intval($versione) > 1) { ?>
  <a class="btn btn-info"  href="./dettagli_percorso.php?cp=<?php echo $cod_percorso;?>&v=<?php echo (intval($versione)-1);?>"> 
  <i class="fa-solid fa-arrow-up"></i> Visualizza versione precedente </a>
<?php } ?>
<?php if ($check_versione_successiva==1) { ?>
  <a class="btn btn-info"  href="./dettagli_percorso.php?cp=<?php echo $cod_percorso;?>&v=<?php echo (intval($versione)+1);?>"> 
  <i class="fa-solid fa-arrow-down"></i> Visualizza versione successiva </a>
<?php } ?>

</h3>





<?php
echo '<ul class="mt-1">';
while($r = pg_fetch_assoc($result)) {
  echo '<li class="mt-1"><b> Codice percorso </b>'.$r["cod_percorso"].'</li>';
  echo '<li class="mt-1"><b> Versione percorso </b>'.$versione.'</li>';
  $desc=$r["descrizione"];


  
  echo '<li class="mt-1"><b> Descrizione </b>'.$r["descrizione"].' ';
  if($check_versione_successiva==0 and $check_edit==1 and $r["flg_disattivo"]==0){
      echo '- <button type="button" class="btn btn-sm btn-info" title="Modifica descrizione" 
      data-bs-toggle="modal" data-bs-target="#edit_desc">
      <i class="fa-solid fa-pencil"></i></button>';
  }
  echo '</li>'; 
  echo '<li class="mt-1"><b> Turno </b>'.$r["cod_turno"].'</li>';
  echo '<li class="mt-1"><b> Durata </b>'.$r["durata"].'</li>';
  echo '<li class="mt-1"><b> Frequenza </b>'.$r["descrizione_long"].'</li>';
  echo '<li class="mt-1"><b> Data attivazione testata (inclusa) </b>'.$r["data_inizio_print"].'</li>';
  $tomorrow = new DateTime('tomorrow');
  $today = new DateTime('today');
  echo '<li class="mt-1"><b> Data disattivazione (esclusa*) </b>'.$r["data_disattivazione_testata"]. ' ';
  if($check_versione_successiva==0 and $check_superedit==1 and $r["flg_disattivo"]==0){
      echo '- <button type="button" class="btn btn-sm btn-info" title="Modifica data disattivazione" 
      data-bs-toggle="modal" data-bs-target="#edit_dd">
      <i class="fa-solid fa-pencil"></i></button>';
  }
  if ($r["flg_disattivo"]==1){
    echo ' - <b><font color=red>Percorso disattivo</font></b>';
  }
  echo '</li>';
  echo '<li>Ultima modifica il '.$r['data_ultima_modifica'].'</li>';
  $freq_sit=$r["freq_testata"];
  $freq_uo=$r["freq_binaria"];
  $id_servizio_uo=$r['id_servizio_uo'];
  $id_servizio_sit= $r['id_servizio_sit'];
  $tipo= $r['id_tipo'];
  $turno=$r["id_turno"];
  $data_attivazione_testata=$r['data_inizio_validita'];
  $data_disattivazione_testata=$r['data_disattivazione_testata'];

  if($r["flg_in_attivazione"]==1){
    $check_in_attivazione=1;
  }
}
echo '</ul>';





?>

<!-- Modal -->
<div class="modal fade" id="edit_desc" tabindex="-1" aria-labelledby="edit_descLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="edit_descLabel">Modifica descrizione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      

        <form name="bilat" method="post" autocomplete="off" action="./backoffice/update_descrizione.php">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">

        <div class="form-group col-md-6">
          <label for="desc"> Descrizione </label> <font color="red">*</font>
          <input type="text" name="desc" id="desc" maxlength="60" class="form-control" value="<?php echo $desc;?>" required="">
        </div>

        <br>
        
        
        <div class="row">
        <button type="submit" class="btn btn-info">
        <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
        </button>
        </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <!--button type="button" class="btn btn-primary">Save changes</button-->
      </div>
    </div>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="edit_dd" tabindex="-1" aria-labelledby="edit_dd_Label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="edit_dd_Label">Modifica data disattivazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      

        <form name="bilat" method="post" autocomplete="off" action="./backoffice/update_data_disattivazione.php">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">



        <?php 
        $tomorrow = new DateTime('tomorrow');
        ?>
        

        <div class="form-group  col-md-6">
          <label for="data_inizio" >Nuova data disattivazione (GG/MM/AAAA) </label><font color="red">*</font>
              <input name="data_disatt" id="js-date" type="text" class="form-control" value="<?php echo $tomorrow->format('d/m/Y');?>" required="">
              <div class="input-group-addon">
                  <span class="glyphicon glyphicon-th"></span>
              </div>

        </div>




        <br>
        
        
        <div class="row">
        <button type="submit" class="btn btn-info">
        <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
        </button>
        </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <!--button type="button" class="btn btn-primary">Save changes</button-->
      </div>
    </div>
  </div>
</div>




<?php

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


$query_anomalie_sit = "select dpsu.*,  /*p.data_attivazione ,*/p.versione as versione_sit,
date_trunc('day',p.data_dismissione) as  data_dismissione 
from anagrafe_percorsi.date_percorsi_sit_uo dpsu
join elem.percorsi p on p.id_percorso = dpsu.id_percorso_sit 
where dpsu.cod_percorso in (
	select /*count(id_percorso_sit),*/ cod_percorso/*, data_fine_validita*/
	from anagrafe_percorsi.date_percorsi_sit_uo dpsu1  
	where data_inizio_validita != data_fine_validita 
	group by cod_percorso, data_fine_validita 
	having count(id_percorso_sit) > 1)
	and dpsu.cod_percorso = $1
order by 2,1";

$result_anomalie_sit = pg_prepare($conn, "query_anomalie_sit", $query_anomalie_sit);
$result_anomalie_sit = pg_execute($conn, "query_anomalie_sit", array($cod_percorso));  
$check_anomalie=0;
while($r_as = pg_fetch_assoc($result_anomalie_sit)) {
  $check_anomalie=1;
  echo '<br><br><i class="fa-solid fa-triangle-exclamation"></i> ';
  echo '<b> Anomalia sulla tabella anagrafe_percorsi.date_percorsi_sit_uo da correggere </b>';
  echo '<ul>';
  ?>




  <?php
  echo '<li> <i class="fa-solid fa-question"></i> Data attivazione da controllare versione ' .$r_as['versione_sit'] .' del SIT: '.$r_as['data_inizio_validita'] .'</li>';
  echo '<li> <i class="fa-solid fa-question"></i> Data disattivazione da controllare versione ' .$r_as['versione_sit'] .' del SIT: '.$r_as['data_fine_validita'] .'</li>';
  echo '<li> <i class="fa-solid fa-check"></i> Data dismissione da SIT versione ' .$r_as['versione_sit'] .' del SIT: '.$r_as['data_dismissione'] .'</li>';
  echo '</ul>';
}

if ($check_anomalie==1){
  echo '<i class="fa-solid fa-list-check"></i> Per correggere occorre modificare la dara di attivzione / disattivazione della tabella 
  anagrafe_percorsi.date_percorsi_sit_uo';
}



?>
<hr>
<h3> Risorse umane e risorse tecniche </h3>
<?php
$mezzo='';
$query0="select 
u.descrizione as ut,
pu.id_squadra,
concat(s.cod_squadra, ' - ',s.desc_squadra) as squadra,
pu.cdaog3,
a.categoria as mezzo,
pu.responsabile, 
pu.solo_visualizzazione, 
pu.rimessa, 
pu.data_attivazione, 
pu.data_disattivazione, 
u.id_ut
from anagrafe_percorsi.percorsi_ut pu 
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
left join elem.automezzi a on a.cdaog3 = pu.cdaog3 
join elem.squadre s on s.id_squadra = pu.id_squadra 
join topo.ut u on u.id_ut = cmu.id_uo_sit  
where pu.cod_percorso = $1  and pu.data_disattivazione = $2";

// RIMESSA / SEDE OPERATIVA
$query_rimessa=$query0 ." and rimessa = 'S' and responsabile = 'N'";
$result1 = pg_prepare($conn, "query_rimessa", $query_rimessa);
$result1 = pg_execute($conn, "query_rimessa", array($cod_percorso, $data_disattivazione_testata));
echo '<ul>';
while($r1 = pg_fetch_assoc($result1)) {
  echo '<h4><li class="mt-1"><b> Sede operativa </b>'.$r1["ut"].'</li></h4>';
  echo '<li class="mt-1"><b> Id Squadra </b>'.$r1["squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li class="mt-1"><b> Mezzo </b>'.$r1["mezzo"].'</li>';
  echo '<li class="mt-1"><b> Responsabile </b>'.$r1["responsabile"].'</li>';
  echo '<li class="mt-1"><b> Solo visualizzazione </b>'.$r1["solo_visualizzazione"].'</li>';
  echo '<li class="mt-1"><b> Data attivazione </b>'.$r1["data_attivazione"].'</li>';
  echo '<li class="mt-1"><b> Data disattivazione  </b>'.$r1["data_disattivazione"].'</li>';
  $rimessa=$r1["id_ut"];
  $sq_rimessa=$r1["id_squadra"];
  $mezzo=$r1["cdaog3"];

}
echo '</ul>';


// GRUPPO DI COORDINAMENTO

//$query_ut=$query0 ." and pu.id_squadra!= 15 and (rimessa = 'N' and responsabile = 'N') or ";
$query_ut=$query0 ." AND responsabile = 'S' and pu.id_squadra!= 15 ";
$result2 = pg_prepare($conn, "query_ut", $query_ut);
$result2 = pg_execute($conn, "query_ut", array($cod_percorso, $data_disattivazione_testata));
//echo '<hr>';
if (pg_num_rows($result2) > 0){
  echo '<h4> <b>Gruppo di coordinamento</b></h4>';
} else {
  echo '<h4> <b>Nessun Gruppo di Coordinamento</b></h4>';
}
echo '<ul>';
while($r2 = pg_fetch_assoc($result2)) {
  echo '<h4><li class="mt-1"><b> Gruppo di coordinamento</b> '.$r2["ut"].'</li></h4>';
  echo '<li class="mt-1"><b> Id Squadra </b>'.$r2["squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li class="mt-1"><b> Mezzo </b>'.$r2["mezzo"].'</li>';
  echo '<li class="mt-1"><b> Responsabile </b>'.$r2["responsabile"].'</li>';
  if ($r2["responsabile"]=='S'){
    $gc=$r2["id_ut"];
    $sq_gc=$r2["id_squadra"];
    if ($mezzo==''){
      $mezzo=$r2["cdaog3"];
    }
  }
  echo '<li class="mt-1"><b> Solo visualizzazione </b>'.$r2["solo_visualizzazione"].'</li>';
  echo '<li class="mt-1"><b> Data attivazione </b>'.$r2["data_attivazione"].'</li>';
  echo '<li class="mt-1"><b> Data disattivazione  </b>'.$r2["data_disattivazione"].'</li>';
}
echo '</ul>';


// ALtre UT Visualizzazione

$query_ut2=$query0 ." and pu.id_squadra= 15 and rimessa = 'N' order by responsabile desc";
$result2 = pg_prepare($conn, "query_ut2", $query_ut2);
$result2 = pg_execute($conn, "query_ut2", array($cod_percorso, $data_disattivazione_testata));
//echo '<hr>';

if (pg_num_rows($result2) > 0){
  echo '<h4> <b>UT visualizzazione</b></h4>';
}

echo '<ul>';
while($r2 = pg_fetch_assoc($result2)) {
  echo '<h4><li class="mt-1">'.$r2["ut"].'</li></h4>';
  //echo '<li class="mt-1"><b> Id Squadra </b>'.$r2["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li class="mt-1"><b> Mezzo </b>'.$r2["mezzo"].'</li>';
  echo '<li class="mt-1"><b> Responsabile </b>'.$r2["responsabile"].'</li>';
  echo '<li class="mt-1"><b> Data attivazione </b>'.$r2["data_attivazione"].'</li>';
  echo '<li class="mt-1"><b> Data disattivazione  </b>'.$r2["data_disattivazione"].'</li>';
}
echo '</ul>';



if($check_versione_successiva==0){
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

<?php if ($check_superedit==1 and $check_in_attivazione==0){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> <i class="fa-solid fa-arrow-up-from-bracket"></i> Nuova versione
</button>
</div>
<?php } else {
    echo '<i class="fa-solid fa-ghost"></i> Percorso non ancora attivo. Per modifiche contattare <a href="mailto:assterritorio@amiu.genova.it">AssTerritorio</a>.';
}?>
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
<input type="hidden" id="data_attivazione" name="data_attivazione" value="<?php echo $data_attivazione_testata;?>">
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
  where id_ut not in (select distinct cmu.id_uo_sit  from anagrafe_percorsi.percorsi_ut pu 
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
where cod_percorso = $1 and data_disattivazione  =$2)
  order by descrizione  ;";
  
  $result3 = pg_prepare($conn, "query3", $query3);
  $result3 = pg_execute($conn, "query3", array($cod_percorso , $data_disattivazione_testata));
  //$result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r3 = pg_fetch_assoc($result3)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" value="<?php echo $r3['id_ut']?>" ><?php echo $r3['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>

<?php if ($check_superedit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> <i class="fa-solid fa-eye"></i> Aggiungi in visualizzazione
</button>
</div>
<?php }?>
</form>
<?php } else {
    echo '<i class="fa-solid fa-ghost"></i> Non è ultima versione del percorso.. non posso fare modifiche.';
}?>

<hr>
<b> NOTE </b>
<small>* la data di disattivazione si intende esclusa. Il che significa che se indico il 31/12 il 31/12 quel percorso non ci sarà più. Se voglio che sia attiva fino al 31/12 devo disattivarlo il 01/01
</small>
</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>



<script>

$('#js-date').datepicker({
    format: 'dd/mm/yyyy',
    startDate: '+1d', 
    language:'it' 
});

</script>

</body>

</html>