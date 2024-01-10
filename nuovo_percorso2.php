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

$query1="select id, descrizione, lpad(codice_servizio,4,'0') as cod_start, 
id_servizio_uo, coalesce(id_servizio_sit,0) as id_servizio_sit
from anagrafe_percorsi.anagrafe_tipo at2 
where id=$1;";
$result1 = pg_prepare($conn, "query1", $query1);
$result1 = pg_execute($conn, "query1", array($tipo));  
//echo $query1;    
while($r1 = pg_fetch_assoc($result1)) { 
  $cod_start=$r1['cod_start'];
  $id_servizio_uo=$r1['id_servizio_uo'];
  $id_servizio_sit=$r1['id_servizio_sit'];
}

$query2="SELECT lpad(CAST(nvl(max(CAST(substr(ID_PERCORSO,5,4) AS integer)),0)+1 AS varchar(4)),4,'0') AS ID
FROM ANAGR_SER_PER_UO aspu
WHERE LENGTH(ID_PERCORSO)=10 AND SUBSTR(ID_PERCORSO,0,4)=:p1
AND REGEXP_LIKE(substr(ID_PERCORSO,5,4), '^[[:digit:]]+$')";

$result2 = oci_parse($oraconn, $query2);
oci_bind_by_name($result2, ':p1', $cod_start);
oci_execute($result2);
while($r2 = oci_fetch_assoc($result2)) { 
  $id_percorso_parte2=$r2['ID'];
}
oci_free_statement($result2);
// DESCRIZIONE
$desc = $_POST['desc'];

// TURNO
$turno = intval($_POST['turno']);

//echo $turno;

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
// FREQUENZA
$freq = $_POST['freq'];

$query4="select cod_frequenza as freq_sit,
freq_binaria as freq_uo,
descrizione_long 
from etl.frequenze_ok fo 
where cod_frequenza=$1;";
$result4 = pg_prepare($conn, "query4", $query4);
$result4 = pg_execute($conn, "query4", array($freq));  
//echo $query1;    
while($r4 = pg_fetch_assoc($result4)) { 
  $freq_sit=$r4['freq_sit'];
  $freq_uo=$r4['freq_uo'];
  $descrizione_long = $r4['descrizione_long'];
}


$codice_percorso=$cod_start.''.$id_percorso_parte2.''.$id_percorso_turno;
?>
<h3>Testata percorso</h3>
<ul><?php
echo '<li><b>Nuovo codice</b>: '.$codice_percorso.'</li>';
echo '<li><b>Descrizione</b>: '.$desc.'</li>';
echo '<li><b>Frequenza</b>: '.$descrizione_long.'</li>';
echo '<li><b>Durata</b>: '.$durata.'</li>';
?></ul><?php


?>


<hr>


<form name="bilat" method="post" autocomplete="off" action="./backoffice/nuovo_percorso3.php">

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $codice_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
<input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
<input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
<input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
<input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo;?>">


<h4>Sede Operativa (rimessa) se presente</h4>

<div class="row g-3 align-items-center">

<div class="form-group  col-md-6">
  <label for="rim">Rimessa:</label> 
                <select name="rim" id="rim" class="selectpicker show-tick form-control" data-live-search="true" >
                <option name="rim" value="">Seleziona la rimessa</option>
  <?php            
  $query0="select id_ut, descrizione from topo.ut 
  where id_zona in (5) 
  order by descrizione ;";
  $result0 = pg_query($conn, $query0);
  //echo $query1;    
  while($r0 = pg_fetch_assoc($result0)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="rim" value="<?php echo $r0['id_ut']?>" ><?php echo $r0['descrizione'] ?></option>
  <?php } ?>

  </select>            
</div>

<div class="form-group  col-md-6">
  <label for="sq_rim">Squadra rimessa:</label> 
                <select name="sq_rim" id="sq_rim" class="selectpicker show-tick form-control"  data-size="5"  data-live-search="true">
                <option name="sq_rim" value="">Seleziona la squadra della rimessa</option>
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
            
        <option name="sq_rim" value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
  <?php } ?>

  </select>            
</div>




</div>



<h4>Gruppo di coordinamento o UT Responsabile</h4>
<small id="ut" class="form-text text-muted"> Deve sempre esserci un Gruppo di Coordinamento. <b>Per tutti i servizi di raccolta deve essere una Unità territoriale.</b> 
    Nel caso di servizi della sola rimessa (es. Ganci) è la rimessa stessa.</small>
<div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="ut">UT:</label> <font color="red">*</font>
                <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <option name="ut" value="">Seleziona il Gruppo di Coordinamento (UT)</option>
  <?php            
  /*$query1="select id_ut, descrizione from topo.ut 
  where id_zona not in (5) 
  order by descrizione ;";*/


  //Tengo anche le rimesse (poi faccio un controllo dopo)

  $query1="select id_ut, descrizione from topo.ut  
  order by descrizione ;";
  $result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r1 = pg_fetch_assoc($result1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" value="<?php echo $r1['id_ut']?>" ><?php echo $r1['descrizione'] ?></option>
  <?php } ?>

  </select>  
            
</div>


<div class="form-group  col-md-6">
  <label for="sq_ut">Squadra UT:</label> <font color="red">*</font>
                <select name="sq_ut" id="sq_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="true" required="">
                <option name="sq_ut" value="">Seleziona la squadra del Gruppo di Coordinamento</option>
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
            
        <option name="sq_ut" value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
  <?php } ?>

  </select>            
</div>

</div>
<hr>
<div class="row g-3 align-items-center">


<div class="form-group  col-md-4">
  <!--div class="input-group date" data-provide="datepicker"-->
  <label for="data_inizio" >Data attivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_att" id="js-date" type="text" class="form-control" value="" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
  <!--/div-->
</div>

<div class="form-group  col-md-4">
  <!--div class="input-group date" data-provide="datepicker"-->
  <label for="data_inizio" >Data disattivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_disatt" id="js-date1"  type="text" class="form-control" value="31/12/2099" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
  <!--/div-->
</div>
<!--div class="form-group  col-md-4">
<label for="data_inizio" >Comuni</label>
<div-->



<div class="form-group  col-md-6">
  <label for="cdaog3">Mezzo:</label> <font color="red">*</font>
                <select name="cdaog3" id="cdaog3" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <option name="cdaog3" value="">Seleziona la tipologia di mezzo</option>
  <?php            
  $query2="select cdaog3,
  concat(categoria, ' (', nome, ')') as cat_estesa  from elem.automezzi a 
  order by categoria ;";
  $result2 = pg_query($conn, $query2);
  //echo $query1;    
  while($r2 = pg_fetch_assoc($result2)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="cdaog3" value="<?php echo $r2['cdaog3']?>" ><?php echo $r2['cat_estesa'] ?></option>
  <?php } ?>

  </select>            
</div>

</div>


<div class="alert alert-error collapse" role="alert" id="alert_UT">
  <span>
  <p>ATTENZIONE. Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa!</p>
  </span>
</div>
<hr>
<?php if ($check_edit==1){?>
<div class="row g-3 align-items-center">
<button type="submit" class="btn btn-info">
<i class="fa-solid fa-plus"></i> Crea percorso
</button>
</div>
<?php }?>
</form>





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
    startDate: '31/12/2099', 
    language:'it'
});




var bk1 = document.getElementById("rim").value;
var bk2 = document.getElementById("ut").value;


var test = [bk1, bk2];
var res = true; 
for(var i = 0; i < test.length; i++) { 
  if (test.indexOf(test[i], i + 1) >= 0) {
    res = false; 
    break;
  } 
}

if(res){
    //alert("yes");
    document.block_form.submit();
}else{
  alert("ATTENZIONE. Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa");
  $('#alert_UT').show();
  //document.getElementById("block_error").value = "Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa";
}

</script>



</body>

</html>