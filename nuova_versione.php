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
$freq_sit = $_POST['freq_sit'];
$freq_uo = $_POST['freq_uo'];
$new_vers= $_POST['old_vers']+1;

$id_servizio_uo = intval($_POST['id_servizio_uo']);
if($_POST['id_servizio_sit']){
$id_servizio_sit = intval($_POST['id_servizio_sit']);
}
?>
<h3>Testata percorso  versione <?php echo $new_vers;?> </h3>
<!--ul><?php
echo '<li><b>Codice</b>: '.$codice_percorso.'</li>';

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
</div>


<div class="form-group col-md-6">
    <label for="desc"> Descrizione </label> <font color="red">*</font>
    <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $desc?>" required="">
</div>

</div>


<div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="tipo">Turno:</label> <font color="red">*</font>
                <select name="turno" id="turno" class="selectpicker show-tick form-control" data-live-search="true"  required="">
              
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
        if ($r2bis['ID_TURNO']==$turno) {
        echo 'selected ';
      }?>
        value="<?php echo $r2bis['ID_TURNO']?>" ><?php echo $r2bis['DESCR'];?></option>
  <?php } 
   oci_free_statement($result2);
  ?>
 
  </select>            
  </div>







  <?php
  // FREQUENZA 
  ?>
<div class="form-group  col-md-6">
  <label for="freq">Frequenza:</label> 
  <?php if ($id_servizio_sit){ echo  '<font color="red">Per cambi frequenza usare SIT*</font>';}?>
                <select name="freq" id="freq" class="selectpicker show-tick form-control" data-live-search="true" 
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

</select>            
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



<h4>UT responsabile </h4>
<div class="row g-3 align-items-center">


<div class="form-group  col-md-6">
  <label for="ut">UT:</label> <font color="red">*</font>
                <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required="">
                <!--option name="ut" value="">Seleziona la tipologia di servizio</option-->
  <?php            
  $query1="select id_ut, descrizione from topo.ut 
  where id_zona not in (5) 
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