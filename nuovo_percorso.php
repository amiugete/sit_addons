<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="it">

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
if ($check_superedit==0) { 
  require('assenza_permessi.php');
  exit;
}

$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];
?>


<div class="container">


<form id="newpercorso" name="bilat" method="post" autocomplete="off" action="nuovo_percorso2.php">

<div class="row g-3 align-items-center">

<?php
// TIPOLOGIA PERCORSO
?>

<div class="form-group  col-md-6">
  <label for="tipo">Tipologia percorso:</label> <font color="red">*</font>
  <select name="tipo" id="tipo" class="selectpicker show-tick form-control" data-live-search="true" required="" onchange="showDestination(this)">
    <option name="tipo" value="">Seleziona la tipologia di servizio</option>
  <?php            
  $query1="select id, descrizione, id_servizio_sit, codice_servizio, cdr
  from anagrafe_percorsi.anagrafe_tipo at2
  where abilitato=true
  order by descrizione;";
  $result1 = pg_query($conn_sit, $query1);
  //echo $query1;    
  while($r1 = pg_fetch_assoc($result1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="tipo" value="<?php echo $r1['id']?>" data-cdr="<?php echo $r1['cdr']?>" data-sit="<?php echo $r1['id_servizio_sit']?>"><?php echo $r1['descrizione'] .'('.$r1['codice_servizio'].')';?></option>
  <?php } ?>

  </select>
  <input type="hidden" name="id_sit" id="id_sit">
  <input type="hidden" name="cdr" id="cdr">            
  </div>

  <div id="divdesc" class="form-group col-md-6">
    <label class="form-check-label" for="desc"> Descrizione </label> <font color="red">*</font>
    <input type="text" name="desc" id="desc" maxlength="60" class="form-control" required="">
  </div>

  </div>

 <div class="row g-3 align-items-center">
  <div id="destinazioni" class="form-group col-md-12" style="display: none;">
    <?php            
  $queryD="select id_destinazione, destinazione 
  from anagrafiche.destinazioni
    where cdr = true and attivo = true
    order by destinazione;";
  $resultD = pg_query($conn_sit, $queryD);
  //echo $query1;    
  while($rD = pg_fetch_assoc($resultD)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" style="border-color:darkgrey;" name="destinazioni[]" id="dest_<?php echo $rD['id_destinazione']?>" value="<?php echo $rD['id_destinazione']?>">
      <label class="form-check-label" for="inlineCheckbox1"><?php echo $rD['destinazione']?></label>
  </div>
  <?php } ?>
  </div>
 </div>
 
 <?php
  // DESCRIZIONE
  ?>
  <!--div class="col-md-6"--> 
  <div id="spaziodescr" class="row g-3 align-items-center">
    
  <!--div class="form-group col-md-6">
    <label class="form-check-label" for="desc"> Descrizione </label> <font color="red">*</font>
    <input type="text" name="desc" id="desc" maxlength="60" class="form-control" required="">
  </div-->

  </div>






  <?php
  // TURNO
  ?>
  <div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label class="form-check-label" for="tipo">Turno:</label> <font color="red">*</font>
                <select name="turno" id="turno" class="selectpicker show-tick form-control" data-live-search="true"  required=""  onchange="showReferenceDay(this)">
                <option name="turno" value="">Seleziona il turno</option>
  <?php            
  $query2="SELECT ID_TURNO, INIZIO_ORA, FINE_ORA,
  concat(concat(codice_turno, ' --> '), DESCR_ORARIO) AS DESCR
  FROM ANAGR_TURNI at2 
  WHERE DTA_DISATTIVAZIONE > SYSDATE 
  ORDER BY inizio_ora, inizio_minuti, fine_ora,fine_minuti";

  

$result2 = oci_parse($oraconn, $query2);
oci_execute($result2);

  while($r2 = oci_fetch_assoc($result2)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="turno" iniora="<?php echo $r2['INIZIO_ORA']?>" finora="<?php echo $r2['FINE_ORA']?>" value="<?php echo $r2['ID_TURNO']?>" ><?php echo $r2['DESCR'];?></option>
  <?php } 
   oci_free_statement($result2);
  ?>
 
  </select> 
    <div class="form-check" id="refDay" style=" display: none;">
  <input class="form-check-input" type="checkbox" value="-1" id="check_ref_day" name="check_ref_day" <?php if ($check_superedit == 0) {echo 'disabled';} ?>>
  <label class="form-check-label" for="check_ref_day">
    Il turno selezionato è notturno, spuntare la checkbox se l'ora di inizio si riferisce al giorno precedente (es. turno 00:00-03:00 iniziato il alle 00:00 del martedì ma il servizio fa riferito a lunedì).
  </label>
</div>           
  </div>







  <?php
  // FREQUENZA
  ?>

<div class="form-group  col-md-6">
  
  <label class="form-check-label" for="freq">Frequenza:</label> <font color="red">*</font><br>
  <!--select name="freq" id="freq" class="selectpicker show-tick form-control" data-live-search="true" required="">
    <option name="freq" value="">Seleziona la frequenza</option>
    <?php            
    /*$query3="select cod_frequenza, descrizione_long 
    from etl.frequenze_ok fo";
    $result3 = pg_query($conn_sit, $query3);
    //echo $query1;    
    while($r3 = pg_fetch_assoc($result3)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
    */
      ?>
        
    <option name="freq" value="<?php echo $r3['cod_frequenza']?>" ><?php echo $r3['descrizione_long'];?></option>
<?php /*}*/ ?>

  </select-->            

<?php 
$freq_sett='T';
require('freq_sett_component.php');
?>

</div>
</div>
<div class="row g-3 align-items-center">
<div class="form-group  col-md-6">
  <label for="stag">Stagionalità:</label> <!--font color="red">*</font-->
  <select name="stag" id="stag" class="selectpicker show-tick form-control" data-live-search="true"  onchange="showSwitch(this)">
    <option name="stag" value="" selected>Nessuna</option>
    <option name="stag" value="E">Estate</option>
    <option name="stag" value="I">Inverno</option>
  </select>            
</div>
<div class="form-group col-md-6" id="switchstag" style=" padding-top: 2%; display: none;">
    <div class="align-items-center" style="display: inline-flex; white-space:nowrap; margin-bottom: 5px;">
      <label for="switchon"> Switch On </label> <font color="red">*</font>
      <input type="number" placeholder="Giorno" name="switchong" id="gson" max="31" class="form-control">
      <input type="number" placeholder="Mese" name="switchonm" id="mson" max="12" class="form-control">
  </div>
    <div class="align-items-center" style="display: inline-flex; white-space:nowrap;">
      <label for="switchoff"> Switch Off </label> <font color="red">*</font>
      <input type="number" placeholder="Giorno" name="switchoffg" id="gsof" max="31" class="form-control">
      <input type="number" placeholder="Mese" name="switchoffm" id="msof" max="12" class="form-control">
  </div>
  </div>
</div>

<div class="row g-3 align-items-center" style="margin-top: 5px;">
<div class="form-group  col-md-6">
  <!--div class="input-group date" data-provide="datepicker"-->
  <label for="data_inizio" >Data attivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_att" id="js-date" type="text" class="form-control" value="" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
  <!--/div-->
</div>

<div class="form-group  col-md-6">
  <!--div class="input-group date" data-provide="datepicker"-->
  <label for="data_inizio" >Data disattivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_disatt" id="js-date1"  type="text" class="form-control" value="31/12/2099" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
  <!--/div-->
</div>
</div>
<hr>

<?php if ($check_edit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> Procedi con la creazione del servizio
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

  function showReferenceDay(val){
      const iniOra = parseInt(val.options[val.selectedIndex].getAttribute('iniora'))
      const finOra = parseInt(val.options[val.selectedIndex].getAttribute('finora'))
      /*console.log('val è '+ val.value)
      console.log('text è '+ val.options[val.selectedIndex].text)
      console.log('iniora è '+ val.options[val.selectedIndex].getAttribute('iniora'))
      console.log('finora è '+ val.options[val.selectedIndex].getAttribute('finora'))
      console.log('il turno selezionato è '+ val.value)*/
      if (finOra <= '06' && val.value!= 997){
        //escludo il turno 997 che è quello disponibile
        document.getElementById('refDay').style.display = "block";
        //console.log('il turno selezionato è a cavallo di due giorni');
      }else{
        document.getElementById('refDay').style.display = "none";
        //console.log('il turno selezionato NON è a cavallo di due giorni');
      }
    }
  
  function showDestination(val){
    console.log(val.value)
    console.log(val)
    tipo = document.getElementById("tipo");
    opt = tipo.selectedOptions[0];
    divdescr = document.getElementById("divdesc");
    divdest = document.getElementById("spaziodescr");
    console.log(opt.dataset)
    if(opt.dataset.cdr === 't'){
      console.log('è cdr')
      document.getElementById('destinazioni').style.display = "block";
      divdest.appendChild(divdescr);
      /*document.getElementById('rimessa').style.display = "block";
      document.getElementById('sqrimessa').style.display = "block";*/
    } else{
      console.log('NON è cdr')
      document.getElementById('destinazioni').style.display = "none";
      /*document.getElementById('rimessa').style.display = "none";
      document.getElementById('sqrimessa').style.display = "none";*/
    }
  }

  form = document.getElementById("newpercorso");
  form.addEventListener("submit", function(e) {

    // prende tutte le checkbox checked
    let checked = document.querySelectorAll("input[name='destinazioni[]']:checked");
    tipo = document.getElementById("tipo");
    opt = tipo.selectedOptions[0];
    console.log(checked);
    console.log(opt.dataset.cdr)
    // verifico se la lunghezza è 0 vuol dire che non hanno selezionato nulla
    if (checked.length === 0 && opt.dataset.cdr ==='t') {
        e.preventDefault(); // blocca invio form
        alert("E' necessario selezionare almeno una destinazione avendo scelto 'Isole ecologiche' come tipologia percorso.");
        return false;
    }

    let sit = opt.dataset.sit;
    document.getElementById("id_sit").value = sit;

  });

</script>


</div>

<?php

oci_close($oraconn);
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