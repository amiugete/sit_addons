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



<div class="row g-3 align-items-center">

<?php
// TIPOLOGIA PERCORSO
$tipo = $_POST['tipo'];
$codice_percorso=$_POST['id_percorso'];
$desc= $_POST['desc'];
$turno= $_POST['turno'];
$turnoIni= $_POST['turno_ini'];
$turnoFine= $_POST['turno_fine'];
$turnoRefDay = $_POST['turno_ref_day'];
$freq_sit = $_POST['freq_sit'];
$fbin = $_POST['fbin'];
$freq_sett = $_POST['freq_sett'];
$freq_uo = $_POST['freq_uo'];
$new_vers= $_POST['old_vers']+1;
$stag = $_POST['stag'];
if($stag != ''){
  $switchON = $_POST['swon'];
  $switchOng = substr($switchON, 0, 2);
  $switchOnm = substr($switchON, 2);
  $switchOFF = $_POST['swoff'];
  $switchOffg = substr($switchOFF, 0, 2);
  $switchOffm = substr($switchOFF, 2);
}else{
  $stag = null;
  $switchON = null;
  $switchOFF = null;
}
$eko = $_POST['eko'];
//echo $eko."<br>";

$id_servizio_uo = intval($_POST['id_servizio_uo']);
if($_POST['id_servizio_sit']){
$id_servizio_sit = intval($_POST['id_servizio_sit']);
}
?>
<h3>Testata percorso  versione <?php echo $new_vers;?> </h3>
<!--ul><?php
echo '<li><b>giorno</b>: '.$stag.'</li>';
echo '<li><b>giorno</b>: '.$switchOffg.'</li>';
echo '<li><b>mese</b>: '.$switchOffm.'</li>';
echo '<li><b>Codice</b>: '.$codice_percorso.'</li>';

echo '<li><b>turno</b>: '.$turno.'</li>';
echo '<li><b>turno Inizio</b>: '.$turnoIni.'</li>';
echo '<li><b>turno Fine</b>: '.$turnoFine.'</li>';
if(intval($turnoIni) > intval($turnoFine)){
  $checkOraTurno = 1;
  echo '<li><b>turno inizia un giorno e finisce l\'altro</b></li>';
}
//exit();
?></ul--><?php


?>





<form name="nv1" method="post" autocomplete="off" action="./backoffice/nuova_versione2.php">

<!--input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $codice_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>"-->
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
<input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
<!--input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>"-->
<!--input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>"-->
<input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo;?>">
<input type="hidden" id="new_vers" name="new_vers" value="<?php echo $new_vers;?>">

<div class="row g-3 align-items-center">

<div class="form-group col-md-6">
    <label for="id_percorso"> Codice percorso </label> <font color="red">*</font>
    <input type="text" name="id_percorso" id="id_percorso" class="form-control" value="<?php echo $codice_percorso?>" readonly="" required="">

    <hr>
    <label for="desc"> Descrizione </label> <font color="red">*</font>
    <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $desc?>" required="">


    <hr>
  <label for="tipo">Turno:</label> <font color="red">*</font>
    <select name="turno" id="turno" class="selectpicker show-tick form-control" data-live-search="true"  required="" onchange="showReferenceDay(this)">
              
  <?php            
  $query2bis="SELECT ID_TURNO, INIZIO_ORA, FINE_ORA,
  concat(concat(codice_turno, ' --> '), DESCR_ORARIO) AS DESCR
  FROM ANAGR_TURNI at2 
  WHERE DTA_DISATTIVAZIONE > SYSDATE 
  ORDER BY inizio_ora, inizio_minuti, fine_ora,fine_minuti";

  

$result2bis = oci_parse($oraconn, $query2bis);
oci_execute($result2bis);

  while($r2bis = oci_fetch_assoc($result2bis)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="turno" iniora="<?php echo $r2bis['INIZIO_ORA']?>" finora="<?php echo $r2bis['FINE_ORA']?>"
        <?php
        if ($r2bis['ID_TURNO']==$turno) {
        echo 'selected ';
      }?>
        value="<?php echo $r2bis['ID_TURNO']?>" ><?php echo $r2bis['DESCR'];?></option>
  <?php } 
   oci_free_statement($result2bis);
  ?>
 
  </select>            
  
  <div class="form-check" id="refDay" style=" display: none;">
  <input class="form-check-input" type="checkbox" value="-1" <?php 
  if ($turnoRefDay == -1){
    echo ' checked ';
  }
  ?> id="check_ref_day" name="check_ref_day" <?php if ($check_superedit == 0) {echo 'disabled';} ?>>
  <label class="form-check-label" for="check_ref_day">
    Il turno selezionato è a cavallo di due giorni, spuntare la checkbox se l'ora di inizio si riferisce al giorno precedente
  </label>
</div>
<hr>
  <!--div class="form-check">
  <input class="form-check-input" type="checkbox" value="t" <?php 
  if ($eko =="t"){
    echo ' checked ';
  }
  ?> id="check_EKO" name="check_EKO" <?php if ($check_superedit == 0) {echo 'disabled';} ?>>
  <label class="form-check-label" for="check_EKO">
    Il percorso verrà automaticamente trasferito anche a Ekovision <i class="fa-solid fa-circle-nodes"></i> (per disabilitare scrivere a </i>assterritorio</i>)
  </label>
</div-->
  
</div>







  <?php
  // FREQUENZA 


  $query3a="select cod_frequenza, descrizione_long 
  from etl.frequenze_ok fo where cod_frequenza = $1;";
  $result3a = pg_prepare($conn, "query3a", $query3a);
  $result3a = pg_execute($conn, "query3a", array($freq_sit));
  while($r3a = pg_fetch_assoc($result3a)) { 
    $freq_long = $r3a['descrizione_long'];
  }

  ?>
<div class="form-group  col-md-6">
  <label for="freq">Frequenza (attuale :   <?php echo $freq_long;?> )</label> 
  <br>
  <br>
  
  
  
  
  <?php if ($id_servizio_sit){ echo  '<!--font color="red">Per cambi frequenza usare SIT*</font-->';}?>
                <!--select name="freq" id="freq" class="selectpicker show-tick form-control" data-live-search="true" 
                <?php if ($id_servizio_sit){ echo ' disabled="true" ';}?> 
                required="">

  <?php            
  $query3bis="select cod_frequenza, descrizione_long 
from etl.frequenze_ok fo;";
$result3bis = pg_prepare($conn, "result3bis", $query3bis);
$result3bis = pg_execute($conn, "result3bis", array());
//echo $query1;    
while($r3bis = pg_fetch_assoc($result3bis)) { 
    //$valore=  $r2['id_via']. ";".$r2['desvia'];            
?>
          
      <option name="freq" id="freq"  <?php
      if ($r3bis['cod_frequenza']==$freq_sit) {
        echo 'selected ';
      }  
      ?> value="<?php echo $r3bis['cod_frequenza']?>" 
      ><?php echo $r3bis['descrizione_long'];?></option>
<?php } ?>

</select-->



<?php require('freq_sett_component.php');?>






<font color="red"><i class="fa-solid fa-triangle-exclamation"></i><b>ATTENZIONE</b> in questo momento si applica la nuova frequenza scelta a tutte le aste o piazzole </font>
<br> <i class="fa-solid fa-person-digging"></i> Modifica in corso     
<hr>       
</div>

<!--div class="row g-3 align-items-center"-->
<div class="form-group  col-md-6" style=" padding-bottom: 2%;">
  <label for="stag">Stagionalità:</label>
  <select name="stag" id="stag" class="selectpicker show-tick form-control" data-live-search="true"  onchange="showSwitch(this)">
    <option name="stag" value="" <?php if($stag ==''){echo 'selected';}?>>Nessuna</option>
    <option name="stag" value="E" <?php if($stag =='E'){echo 'selected';}?>>Estate</option>
    <option name="stag" value="I" <?php if($stag =='I'){echo 'selected';}?>>Inverno</option>
  </select>            
</div>
<div class="form-group col-md-6" id="switchstag" style=" display: none;">
    <div class="align-items-center" style="display: inline-flex; white-space:nowrap; margin-bottom: 5px;">
      <label for="switchon"> Switch On </label> <font color="red">*</font>
      <input type="number" placeholder="Giorno" name="switchong" id="gson" max="31" class="form-control" value="<?php if($stag !=''){echo $switchOng;}?>">
      <input type="number" placeholder="Mese" name="switchonm" id="mson" max="12" class="form-control" value="<?php if($stag !=''){echo $switchOnm;}?>">
  </div>
    <div class="align-items-center" style="display: inline-flex; white-space:nowrap;">
      <label for="switchoff"> Switch Off </label> <font color="red">*</font>
      <input type="number" placeholder="Giorno" name="switchoffg" id="gsof" max="31" class="form-control" value="<?php if($stag !=''){echo $switchOffg;}?>">
      <input type="number" placeholder="Mese" name="switchoffm" id="msof" max="12" class="form-control" value="<?php if($stag !=''){echo $switchOffm;}?>">
  </div>
  </div>
<!--/div-->

  <div class="form-check">
  <input class="form-check-input" type="checkbox" value="t" <?php 
  if ($eko =="t"){
    echo ' checked ';
  }
  ?> id="check_EKO" name="check_EKO" <?php if ($check_superedit == 0) {echo 'disabled';} ?>>
  <label class="form-check-label" for="check_EKO">
    Il percorso verrà automaticamente trasferito anche a Ekovision <i class="fa-solid fa-circle-nodes"></i> (per disabilitare scrivere a </i>assterritorio</i>)
  </label>
</div>

</div>




<hr>
<h4>Sede Operativa (rimessa) se presente</h4>

<div class="row g-3 align-items-center">

<div class="form-group  col-md-6">
  <label for="rim">Rimessa: <?php echo $_POST["rimessa"]; ?></label> 
                <select name="rim" id="rim" class="selectpicker show-tick form-control" data-live-search="true" >
                <?php if (!$_POST["rimessa"]){?>
                <option name="rim" value="">Seleziona la rimessa</option>
                <?php }?>
  <?php            
  $query0="select id_ut, descrizione from topo.ut 
  where id_zona in (5) 
  order by descrizione ;";
  $result0 = pg_query($conn, $query0);
  //echo $query1;    
  while($r0 = pg_fetch_assoc($result0)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="rim"
        <?php 
        if ($_POST["rimessa"]==$r0['id_ut']){
          echo ' selected ';
        } ?>
        value="<?php echo $r0['id_ut']?>" ><?php echo $r0['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>

<div class="form-group  col-md-6">
  <label for="sq_rim">Squadra rimessa:</label> 
                <select name="sq_rim" id="sq_rim" class="selectpicker show-tick form-control"  data-size="5"  data-live-search="true">
                <?php if (!$_POST["rimessa"]){?>
                  <option name="sq_rim" value="">Seleziona la squadra della rimessa</option>
                <?php } ?>
  <?php            
  $query0_1="select id_squadra, 
  concat(cod_squadra, ' - ', desc_squadra) as descr 
  from elem.squadre s order by desc_squadra ;";

  $result0_1 = pg_prepare($conn, "query0_1", $query0_1);
  $result0_1 = pg_execute($conn, "query0_1", array());  
  //echo $query1;    
  while($r0_1 = pg_fetch_assoc($result0_1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="sq_rim" 
        <?php 
        if ($_POST["sq_rimessa"]==$r0_1['id_squadra']){
          echo ' selected ';
        } ?>
        value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
  <?php } ?>

  </select>            
</div>




</div>


<hr>
<h4>Gruppo di coordinamento o UT Responsabile</h4>
<small id="ut" class="form-text text-muted"> Deve sempre esserci un Gruppo di Coordinamento. <b>Per tutti i servizi di raccolta deve essere una Unità territoriale.</b> 
    Nel caso di servizi della sola rimessa (es. Ganci) è la rimessa stessa.</small>
<div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="ut">UT (o Rimessa):</label> <font color="red">*</font>
                <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <!--option name="ut" value="">Seleziona la tipologia di servizio</option-->
                <?php if (!$_POST["gc"]){?>
                  <option name="ut" value="">Seleziona il GC</option>
                <?php } ?>
  <?php            
  $query1="select id_ut, descrizione from topo.ut 
  /*where id_zona not in (5) */
  order by descrizione ;";
  $result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r1 = pg_fetch_assoc($result1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" 
        <?php 
        if ($_POST["gc"]==$r1['id_ut']){
          echo ' selected ';
        } ?>
        value="<?php echo $r1['id_ut']?>" ><?php echo $r1['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>


<div class="form-group  col-md-6">
  <label for="sq_ut">Squadra UT:</label> <font color="red">*</font>
                <select name="sq_ut" id="sq_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="true" required="">
                <?php if (!$_POST["gc"]){?>
                  <option name="ut" value="">Seleziona la squadra del GC</option>
                <?php } ?>
                <!--option name="sq_ut" value="">Seleziona la squadra della rimessa</option-->
  <?php            
  /*$query0_1="select id_squadra, 
  concat(cod_squadra, ' - ', desc_squadra) as descr 
  from elem.squadre s order by desc_squadra ;";
  $result0_1 = pg_query($conn, $query0_1);
  //echo $query1; */   
  $result0_1 = pg_execute($conn, "query0_1", array()); 
  while($r0_1 = pg_fetch_assoc($result0_1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="sq_ut" 
        <?php 
        if ($_POST["sq_gc"]==$r0_1['id_squadra']){
          echo ' selected ';
        } ?>
        value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
  <?php } ?>

  </select>            
</div>

</div>
<hr>
<div class="row g-3 align-items-center">

<?php 
$tomorrow = new DateTime('tomorrow');
?>

<div class="form-group  col-md-6">
  <label for="data_inizio" >Data attivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_att" id="js-date" type="text" class="form-control" value="<?php echo $tomorrow->format('d/m/Y');?>" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>

</div>



<?php



$query_data_disattivazione="SELECT DISTINCT  to_char(DTA_DISATTIVAZIONE, 'DD/MM/YYYY') as DATA_DIS
FROM ANAGR_SER_PER_UO aspu 
WHERE ID_PERCORSO = :c1 AND DTA_DISATTIVAZIONE > SYSDATE";



$result_dd = oci_parse($oraconn, $query_data_disattivazione);
# passo i parametri
oci_bind_by_name($result_dd, ':c1', $codice_percorso);
oci_execute($result_dd);

while($rdd = oci_fetch_assoc($result_dd)) { 
  $data_disattivazione= $rdd['DATA_DIS'];
}
oci_free_statement($result_dd);
?>



<div class="form-group  col-md-6">
  <label for="data_inizio" >Data disattivazione (GG/MM/AAAA) </label><font color="red">*</font>
    <input name="data_disatt" id="js-date1"  type="text" class="form-control" value="<?php echo $data_disattivazione;?>" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
</div>

<!--div class="form-group  col-md-4">
<label for="data_inizio" >Comuni</label>
<div-->


<hr>
<div class="form-group  col-md-6">
  <label for="cdaog3">Mezzo:</label> <font color="red">*</font>
                <select name="cdaog3" id="cdaog3" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <?php if ($_POST["mezzo"]=='') { ?>
                <option name="cdaog3" value="">Seleziona la tipologia di mezzo</option>
                <?php } ?>
  <?php            
  $query2="select cdaog3,
  concat(categoria, ' (', nome, ')') as cat_estesa  from elem.automezzi a 
  order by categoria ;";
  $result2 = pg_query($conn, $query2);
  //echo $query1;    
  while($r2 = pg_fetch_assoc($result2)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="cdaog3" 
        <?php
        if ($_POST["mezzo"]==$r2['cdaog3']){
          echo ' selected ';
        } ?>
        value="<?php echo $r2['cdaog3']?>" ><?php echo $r2['cat_estesa'] ?></option>
  <?php } ?>

  </select>            
</div>

</div>



<hr>
<?php if ($check_edit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> Nuova versione
</button>
</div>
<?php }?>
</form>



<script type="text/javascript">

  function showSwitch(val){
    console.log(val.value)
    if(val.value!=''){
      document.getElementById('switchstag').style.display = "block";
      document.getElementById('gson').setAttribute("required", "");
      document.getElementById('mson').setAttribute("required", "");
      document.getElementById('gsof').setAttribute("required", "");
      document.getElementById('msof').setAttribute("required", "");
    } else{
      document.getElementById('switchstag').style.display = "none";
      document.getElementById('gson').removeAttribute('required');
      document.getElementById('mson').removeAttribute('required');
      document.getElementById('gsof').removeAttribute('required');
      document.getElementById('msof').removeAttribute('required');
    }
  }

   function aggiornaVisibilita() {
      const stagSel = document.getElementById('stag').value;

      if (stagSel != '') {
        document.getElementById('switchstag').style.display = "block";
      } else{
        document.getElementById('switchstag').style.display = "none";
      }
    }

    // Esegui la funzione al caricamento della pagina
    window.addEventListener('DOMContentLoaded', aggiornaVisibilita);

    function showReferenceDay(val){
      const iniOra = val.options[val.selectedIndex].getAttribute('iniora')
      const finOra = val.options[val.selectedIndex].getAttribute('finora')
      if (iniOra > finOra){
        document.getElementById('refDay').style.display = "block";
      }else{
        document.getElementById('refDay').style.display = "none";
      }
      /*console.log('text è '+ val.options[val.selectedIndex].text)
      console.log('iniora è '+ val.options[val.selectedIndex].getAttribute('iniora'))
      console.log('finora è '+ val.options[val.selectedIndex].getAttribute('finora'))
      console.log('il turno selezionato è '+ val.value)*/
    }

    function aggiornaRefDay() {
      const checkRefDay = document.getElementById('check_ref_day');
      const selTurno = document.getElementById('turno');
      const iniOra = selTurno.options[selTurno.selectedIndex].getAttribute('iniora')
      const finOra = selTurno.options[selTurno.selectedIndex].getAttribute('finora')
      if (iniOra > finOra){
        document.getElementById('refDay').style.display = "block";
        console.log('il turno è notturno. Inizia alle '+ iniOra + ' e finisce alle '+ finOra)
      }else{
        document.getElementById('refDay').style.display = "none";
        console.log('il turno NON è notturno.')
      }
    }
    window.addEventListener('DOMContentLoaded', aggiornaRefDay);

</script>

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

$('#js-date1').datepicker({
    format: 'dd/mm/yyyy',
    language:'it'
});
</script>



</body>

</html>