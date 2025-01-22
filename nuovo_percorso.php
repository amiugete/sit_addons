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


<form name="bilat" method="post" autocomplete="off" action="nuovo_percorso2.php">

<div class="row g-3 align-items-center">

<?php
// TIPOLOGIA PERCORSO
?>

<div class="form-group  col-md-6">
  <label for="tipo">Tipologia percorso:</label> <font color="red">*</font>
                <select name="tipo" id="tipo" class="selectpicker show-tick form-control" data-live-search="true" required="">
                <option name="tipo" value="">Seleziona la tipologia di servizio</option>
  <?php            
  $query1="select id, descrizione, codice_servizio
  from anagrafe_percorsi.anagrafe_tipo at2;";
  $result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r1 = pg_fetch_assoc($result1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="tipo" value="<?php echo $r1['id']?>" ><?php echo $r1['descrizione'] .'('.$r1['codice_servizio'].')';?></option>
  <?php } ?>

  </select>            
  </div>
 
 
 
 
 <?php
  // DESCRIZIONE
  ?>
  <!--div class="col-md-6"--> 
  <div class="form-group col-md-6">
    <label for="desc"> Descrizione </label> <font color="red">*</font>
    <input type="text" name="desc" id="desc" maxlength="60" class="form-control" required="">
  </div>

  </div>






  <?php
  // TURNO
  ?>
  <div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="tipo">Turno:</label> <font color="red">*</font>
                <select name="turno" id="turno" class="selectpicker show-tick form-control" data-live-search="true"  required="">
                <option name="turno" value="">Seleziona il turno</option>
  <?php            
  $query2="SELECT ID_TURNO, 
  concat(concat(codice_turno, ' --> '), DESCR_ORARIO) AS DESCR
  FROM ANAGR_TURNI at2 
  WHERE DTA_DISATTIVAZIONE > SYSDATE 
  ORDER BY inizio_ora, inizio_minuti, fine_ora,fine_minuti";

  

$result2 = oci_parse($oraconn, $query2);
oci_execute($result2);

  while($r2 = oci_fetch_assoc($result2)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="turno" value="<?php echo $r2['ID_TURNO']?>" ><?php echo $r2['DESCR'];?></option>
  <?php } 
   oci_free_statement($result2);
  ?>
 
  </select>            
  </div>







  <?php
  // FREQUENZA
  ?>
<div class="form-group  col-md-6">
  <label for="freq">Frequenza:</label> <font color="red">*</font>
                <select name="freq" id="freq" class="selectpicker show-tick form-control" data-live-search="true" required="">
                <option name="freq" value="">Seleziona la frequenza</option>
  <?php            
  $query3="select cod_frequenza, descrizione_long 
from etl.frequenze_ok fo";
$result3 = pg_query($conn, $query3);
//echo $query1;    
while($r3 = pg_fetch_assoc($result3)) { 
    //$valore=  $r2['id_via']. ";".$r2['desvia'];            
?>
          
      <option name="freq" value="<?php echo $r3['cod_frequenza']?>" ><?php echo $r3['descrizione_long'];?></option>
<?php } ?>

</select>            

<?php 
$freq_sett='T';
require('freq_sett_component.php');
?>

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






</div>

<?php

oci_close($oraconn);
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>