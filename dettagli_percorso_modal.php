<?php
//session_set_cookie_params($lifetime);
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<?php
$check_modal = 1;

require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}



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


<div id="modal_dettagli">



<?php
$query_testata = "select ep.cod_percorso, 
ep.descrizione, t.cod_turno, t.id_turno, ep.durata, fo.descrizione_long, 
ep.freq_testata, ep.freq_testata::bit(12) as fbin, fo.freq_binaria, ep.id_tipo, ep.freq_settimane,
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
to_char(ep.data_ultima_modifica, 'DD/MM/YYYY HH24:MI') as data_ultima_modifica, 
ep.ekovision
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
<!--a class="btn btn-info"  href="./percorsi.php"> <i class="fa-solid fa-table-list"></i> Torna a elenco percorsi </a-->
<?php if (intval($versione) > 1) { ?>
  <!--a class="btn btn-info"  href="./dettagli_percorso.php?cp=<?php echo $cod_percorso;?>&v=<?php echo (intval($versione)-1);?>"> 
  <i class="fa-solid fa-arrow-up"></i> Visualizza versione precedente </a-->
<?php } ?>
<?php if ($check_versione_successiva==1) { ?>
  <!-- class="btn btn-info"  href="./dettagli_percorso.php?cp=<?php echo $cod_percorso;?>&v=<?php echo (intval($versione)+1);?>"> 
  <i class="fa-solid fa-arrow-down"></i> Visualizza versione successiva </a-->
<?php } ?>

</h3>





<?php
echo '<ul class="mt-1">';
while($r = pg_fetch_assoc($result)) {
  echo '<li class="mt-1"><b> Codice percorso </b>'.$r["cod_percorso"].'</li>';
  echo '<li class="mt-1"><b> Versione percorso </b>'.$versione.'</li>';
  $desc=$r["descrizione"];

  if($r["flg_in_attivazione"]==1){
    $check_in_attivazione=1;
  }
  
  if($check_versione_successiva==0 and $check_edit==1 and $r["flg_disattivo"]==0){
    ?>
    <li><b>Descrizione</b>
    <!--form class="row row-cols-lg-auto g-3 align-items-center" name="form_desc" method="post" autocomplete="off" action="./backoffice/update_descrizione.php"-->
    <form class="row row-cols-lg-auto g-3 align-items-center" name="form_desc" id="form_desc" autocomplete="off">
    <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
    <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">



    <?php
    # non serve più 
    $tomorrow = new DateTime('tomorrow');
    ?>
    
    <div class="col-12">
    <div class="input-group">
  
          <input type="text" name="desc" id="desc" maxlength="60" class="form-control" value="<?php echo $desc;?>" required="">
          

    </div>
    </div>



    <br>
    
    <div class="col-12">
    
    <button type="submit" class="btn btn-info">
    <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
    </button>
    </div> 
  </form>
<!-- lancio il form e scrivo il risultato -->
<p><div id="results_desc"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_desc').submit(function (event) { 
                    event.preventDefault();                  
                    console.log('Bottone form dd cliccato e finito qua');
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/update_descrizione.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_desc").html(response).fadeIn("slow");
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>
      <?php 
  } else {
    echo '<li class="mt-1"><b> Descrizione </b>'.$r["descrizione"].' ';
  }

  if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $r["flg_disattivo"]==0){
    ?>
    <li><b>Turno</b>
    <!--form class="row row-cols-lg-auto g-3 align-items-center" name="form_dd" method="post" autocomplete="off" action="./backoffice/update_data_attivazione.php"-->
    <form class="row row-cols-lg-auto g-3 align-items-center" name="form_turno" id="form_turno" autocomplete="off" >
    <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
    <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">

    
    <div class="col-12">
    <!--div class="input-group"-->
    <!--input type="text" name="turno" id="turno" maxlength="60" class="form-control" value="<?php echo $r["cod_turno"];?>" required=""-->
      <select name="turno" id="turno" class="form-select" data-live-search="true"  required="">
              
              <?php            
              $query2bis="SELECT ID_TURNO, 
              concat(concat(codice_turno, ' --> '), DESCR_ORARIO) AS DESCR
              FROM ANAGR_TURNI at2 
              WHERE DTA_DISATTIVAZIONE > SYSDATE 
              ORDER BY inizio_ora, inizio_minuti, fine_ora,fine_minuti";
            
              
            
            $result2bis = oci_parse($oraconn, $query2bis);
            oci_execute($result2bis);
            
              while($r2bis = oci_fetch_assoc($result2bis)) { 
                  //$valore=  $r2['id_via']. ";".$r2['desvia'];            
              ?>
                        
                    <option name="turno" 
                    <?php
                    if ($r2bis['ID_TURNO']==$r["id_turno"]) {
                    echo 'selected ';
                  }?>
                    value="<?php echo $r2bis['ID_TURNO']?>" ><?php echo $r2bis['DESCR'];?></option>
              <?php } 
               oci_free_statement($result2bis);
              ?>
             
      </select> 

    
    <!--/div-->
    </div>


    <br>
    
    <div class="col-12">
    
    <button type="submit" class="btn btn-info">
    <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
    </button>
    </div> 
  </form>
  <p><div id="results_turno"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_turno').submit(function (event) { 
                    console.log('Bottone form dd cliccato e finito qua');
                    event.preventDefault();                  
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/update_turno.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_turno").html(response).fadeIn("slow");
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>

    <?php
  }else{
    echo '<li class="mt-1"><b> Turno </b>'.$r["cod_turno"].'</li>';
  }
  
  echo '<li class="mt-1"><b> Durata </b>'.$r["durata"].'</li>';
  echo '<li class="mt-1"><b> Frequenza </b>'.$r["descrizione_long"];
  
 if ($r['freq_settimane']=='T'){
  echo '';
 } else if ($r['freq_settimane']=='P') {
  echo ' - Solo settimane Pari';
 } else if ($r['freq_settimane']=='D') {
 echo ' - Solo settimane Dispari';
}
  echo '</li>';




 
  if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $r["flg_disattivo"]==0){
  ?>
    <li><b>Data attivazione testata (inclusa)</b>
    <!--form class="row row-cols-lg-auto g-3 align-items-center" name="form_dd" method="post" autocomplete="off" action="./backoffice/update_data_attivazione.php"-->
    <form class="row row-cols-lg-auto g-3 align-items-center" name="form_da" id="form_da" autocomplete="off" >
    <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
    <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">



    <?php
    # non serve più 
    $tomorrow = new DateTime('tomorrow');
    ?>
    
    <div class="col-12">
    <div class="input-group">
            <input name="data_att" id="js-date1" type="text" class="form-control" value="<?php echo $r["data_inizio_print"];?>" required="">
          <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
          </div>

    </div>
    </div>



    <br>
    
    <div class="col-12">
    
    <button type="submit" class="btn btn-info">
    <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
    </button>
    </div> 
  </form>
<!-- lancio il form e scrivo il risultato -->
<p><div id="results_da"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_da').submit(function (event) { 
                    console.log('Bottone form dd cliccato e finito qua');
                    event.preventDefault();                  
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/update_data_attivazione.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_da").html(response).fadeIn("slow");
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>






      <?php 
  } else { 
    echo '<li class="mt-1"><b> Data attivazione testata (inclusa) </b>'.$r["data_inizio_print"].'';
  }
  echo '</li>';
  $tomorrow = new DateTime('tomorrow');
  $today = new DateTime('today');




  

  if($check_versione_successiva==0 and $check_superedit==1 and $r["flg_disattivo"]==0){
      /*echo '- <button type="button" class="btn btn-sm btn-info" title="Modifica data disattivazione" 
      data-bs-toggle="collapse" data-bs-target="#edit_dd'.$cod_percorso.'_'.$versione.'" aria-expanded="false" aria-controls="edit_dd'.$cod_percorso.'_'.$versione.'">
      <i class="fa-solid fa-pencil"></i></button>';*/
      ?> 
      <!-- Data disattivazione -->
         
      
      <li><b>Data disattivazione (esclusa)</b>
              <!--form class="row row-cols-lg-auto g-3 align-items-center" name="form_dd" method="post" autocomplete="off" action="./backoffice/update_data_disattivazione.php"-->
              <form class="row row-cols-lg-auto g-3 align-items-center" name="form_dd" id="form_dd">

              <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
              <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
      
      
      
              <?php
              # non serve più 
              $tomorrow = new DateTime('tomorrow');
              ?>
              
              <div class="col-12">
              <div class="input-group">
            
                    <!--input name="data_disatt" id="js-date2" type="text" class="form-control" value="<?php echo $tomorrow->format('d/m/Y');?>" required=""-->
                    <input name="data_disatt" id="js-date2" type="text" class="form-control" value="<?php echo $r["data_disattivazione_testata"];?>" required="">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                    <button type="button" id="btnTomorrow" class="btn btn-info btn-sm" title="Imposta a domani"><i class="fa-solid fa-calendar-day"></i></button>
                    <button type="button" id="btnInf" class="btn btn-info btn-sm" title="Imposta all'infinito"><i class="fa-solid fa-calendar-xmark"></i></button>
              </div>
              </div>
      
      
      
              <br>
              
              <div class="col-12">
              
              <button type="submit" class="btn btn-info">
              <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
              </button>
              </div> 

              
            </form>
            
            <!-- lancio il form e scrivo il risultato -->
            <p><div id="results_dd"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_dd').submit(function (event) { 
                    console.log('Bottone form dd cliccato e finito qua');
                    event.preventDefault();                  
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/update_data_disattivazione.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_dd").html(response).fadeIn("slow");
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>






      <?php 
  } else {  echo '<li class="mt-1"><b> Data disattivazione (esclusa*) </b>'.$r["data_disattivazione_testata"]. ' ';
  }
  if ($r["flg_disattivo"]==1){
    echo ' - <b><font color=red>Percorso disattivo</font></b>';
  }
  echo '</li>';
  $eko=$r["ekovision"];
  if ($eko=='t'){
    echo '<li><i class="fa-solid fa-link"></i> Percorso trasmesso a ekovision</li>';
  } else {
    echo '<li><i class="fa-solid fa-link-slash"></i> Percorso non trasmesso a ekovision</li>';
  }
  echo '<li>Ultima modifica il '.$r['data_ultima_modifica'].'</li>';
  $freq_sit=$r["freq_testata"];
  $fbin=$r["fbin"];
  $freq_sett=$r["freq_settimane"];
  $freq_uo=$r["freq_binaria"];
  $id_servizio_uo=$r['id_servizio_uo'];
  $id_servizio_sit= $r['id_servizio_sit'];
  $tipo= $r['id_tipo'];
  $turno=$r["id_turno"];
  $data_attivazione_testata=$r['data_inizio_validita'];
  $data_disattivazione_testata=$r['data_disattivazione_testata'];

  
}
echo '</ul>';





?>



















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
$query0_modal="select 
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
$query_rimessa=$query0_modal ." and rimessa = 'S' and responsabile = 'N'";
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

//$query_ut=$query0_modal ." and pu.id_squadra!= 15 and (rimessa = 'N' and responsabile = 'N') or ";
$query_ut=$query0_modal ." AND responsabile = 'S' and pu.id_squadra!= 15 ";
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

$query_ut2=$query0_modal ." and pu.id_squadra= 15 and rimessa = 'N' order by responsabile desc";
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

<form id="nv" name="nuova_versione" target="_blank" method="post" autocomplete="off" action="./nuova_versione.php">

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="fbin" name="fbin" value="<?php echo $fbin;?>">
<input type="hidden" id="freq_sett" name="freq_sett" value="<?php echo $freq_sett;?>">
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
<input type="hidden" id="eko" name="eko" value="<?php echo $eko;?>">

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

<!--form id="vis" name="vis" method="post" autocomplete="off" action="./backoffice/nuova_visualizzazione.php"-->
<form id="vis" name="vis" autocomplete="off" >

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="freq_sett" name="freq_sett" value="<?php echo $freq_sett;?>">
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
                <select name="ut" id="ut" class="selectpicker show-tick form-control" placeholder= "Seleziona UT in visualizzazione" 
                data-live-search="true" data-size="5" required="">
                <!--option name="ut" value=""></option-->
  <?php            
  

  $query3="select id_ut, descrizione 
  from topo.ut   
  where id_ut not in (select distinct cmu.id_uo_sit  from anagrafe_percorsi.percorsi_ut pu 
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
where cod_percorso = $1 and data_disattivazione  =$2)
  order by descrizione  ;";
  
  $result3 = pg_prepare($conn, "query3", $query3);
  $result3 = pg_execute($conn, "query3", array($cod_percorso , $data_disattivazione_testata));  
  while($r3 = pg_fetch_assoc($result3)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" value="<?php echo $r3['id_ut']?>" ><?php echo $r3['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>
</div> 

<?php if ($check_superedit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> <i class="fa-solid fa-eye"></i> Aggiungi in visualizzazione
</button>
</div>

<?php }?>


</form>
<!-- lancio il form e scrivo il risultato -->
<p><div id="results_vis"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_dd').submit(function (event) { 
                    console.log('Bottone nuova visualizzazione cliccato e finito qua');
                    event.preventDefault();                  
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/nuova_visualizzazione.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_vis").html(response).fadeIn("slow");
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>






<?php } else {
    echo '<i class="fa-solid fa-ghost"></i> Non è ultima versione del percorso.. non posso fare modifiche.';
}?>

<hr>
<b> NOTE </b>
<small>* la data di disattivazione si intende esclusa. Il che significa che se indico il 31/12 il 31/12 quel percorso non ci sarà più. Se voglio che sia attiva fino al 31/12 devo disattivarlo il 01/01
</small>




</div>

<?php
//require_once('req_bottom.php');
//require_once('./footer.php');
?>



<script>

// imposto la data di disattivazione a domani
$('#btnTomorrow').click(function() {     
  $('#js-date2').datepicker('setDate', "+1d");
});

$('#btnInf').click(function() {     
  $('#js-date2').datepicker('setDate', "31/12/2099");
});

$(document).ready(function(){
  console.log('Ci passo?');
  $('#js-date1').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: "linked", // in conflitto con startDate
      startDate: '+1d', 
      language:'it' 
  });


  $('#js-date2').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: 'linked',
      startDate: '+1d', 
      assumeNearbyYear: "true",
      startView: 0 ,
      language:'it' 
  });
}); 

$(document).ready(function(){
    console.log('Arrivo qua');
    //$('#ut').removeAttr('disabled');
    $('#ut').selectpicker("show");
    //$('#ut').selectpicker("refresh");
});



</script>




<script>




</script>

</html>