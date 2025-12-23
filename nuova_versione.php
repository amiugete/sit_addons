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
$old_vers= $_POST['old_vers'];
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
if(!empty($_POST['id_servizio_sit'])){
$id_servizio_sit = intval($_POST['id_servizio_sit']);
}else {
  $id_servizio_sit = "";
}

$cdr = $_POST['cdr'];

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





<form id="newversione" name="nv1" method="post" autocomplete="off" action="./backoffice/nuova_versione2.php">

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
    <label for="tipo_descr"> Tipologia percorso: </label> <font color="red">*</font> 
    <?php            
      $queryT="SELECT id, descrizione, codice_servizio, cdr
      from anagrafe_percorsi.anagrafe_tipo at2
      where id = $1;";
      $resultT = pg_prepare($conn_sit, "queryT", $queryT);
      $resultT = pg_execute($conn_sit, "queryT", array($tipo));
      while($rT = pg_fetch_assoc($resultT)) { ?>
      <input type="text" name="tipo_descr" id="tipo_descr" data-cdr="<?php echo $rT['cdr']?>" class="form-control" value="<?php echo $rT['descrizione']?>" readonly="" required="">
      <?php
      }       
    ?>
    <br>
  </div>
  <div id="destinazioni" class="form-group col-md-6" style="display: none;">
    <?php            
      $queryD="select id_destinazione, destinazione 
      from anagrafiche.destinazioni
        where cdr = true and attivo = true
        order by destinazione;";
      $resultD = pg_query($conn_sit, $queryD);
      //echo $query1;
      $queryChecked = "SELECT * from anagrafe_percorsi.percorsi_destinazione pd 
      where pd.cod_percorso = $1 and pd.versione = $2";
      $resultChecked = pg_prepare($conn_sit, "queryChecked", $queryChecked);
      $resultChecked = pg_execute($conn_sit, "queryChecked", array($codice_percorso, $old_vers));
      $dest_checked = [];
      while ($rc = pg_fetch_assoc($resultChecked)) {
          $dest_checked[] = $rc['id_destinazione'];
      }

      while($rD = pg_fetch_assoc($resultD)) { 
          $id = $rD['id_destinazione'];
          $checkedD = in_array($id, $dest_checked) ? "checked" : "";            
    ?>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" style="border-color:darkgrey;" name="destinazioni[]" id="dest_<?php echo $rD['id_destinazione']?>" value="<?php echo $rD['id_destinazione']?>" <?php echo $checkedD; ?>>
      <label class="form-check-label" for="inlineCheckbox1"><?php echo $rD['destinazione']?></label>
    </div>
    <?php } ?>
  </div>
  <hr>
 </div>

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
     Il turno selezionato è notturno, spuntare la checkbox se l'ora di inizio si riferisce al giorno precedente (es. turno 00:00-03:00 iniziato il alle 00:00 del martedì ma il servizio fa riferito a lunedì).
  </label>
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
  $result3a = pg_prepare($conn_sit, "query3a", $query3a);
  $result3a = pg_execute($conn_sit, "query3a", array($freq_sit));
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
$result3bis = pg_prepare($conn_sit, "result3bis", $query3bis);
$result3bis = pg_execute($conn_sit, "result3bis", array());
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
  <hr>
  <!--div class="row g-3 align-items-center"-->
  <div class="form-group  col-md-12">
    <label for="nota_vers">Nota versione:</label>
    <input type="text" name="nota_vers" id="nota_vers" class="form-control" value="">
  </div>
  <!--/div-->
<!--/div-->
  <hr>
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
<h4>Gruppo di coordinamento o UT Responsabile</h4>
<div class="row g-3 align-items-center">
  <div class="form-group  col-md-6">
    <label for="ut">UT (o Rimessa):</label> <font color="red">*</font>
    <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="false" data-size="5" required="" >
      <?php if (!$_POST["gc"]){?>
        <option name="ut" value="">Nessun GC selezionato</option>
      <?php }
      else{          
        $query1="SELECT id_ut, descrizione from topo.ut 
        where id_ut = $1;";
        $result1 = pg_prepare($conn_sit, "query1", $query1);
        $result1 = pg_execute($conn_sit, "query1", array($_POST["gc"]));
        //echo $query1;    
        while($r1 = pg_fetch_assoc($result1)) {
            $id_ut_sel = $r1['id_ut'];
            //$valore=  $r2['id_via']. ";".$r2['desvia'];            
      ?>
      <option name="ut" value="<?php echo $r1['id_ut']?>" ><?php echo $r1['descrizione'] ?></option>
      <?php }} ?>
    </select>
  </div>

  <div class="form-group  col-md-6">
    <label for="sq_ut">Squadra UT:</label> <font color="red">*</font>
    
      <?php if (!$_POST["gc"]){?>
        <select name="sq_ut" id="sq_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="false" required="">
          <option name="ut" value="">Nessun GC selezionato</option>
        </select>
      <?php } else{?>
        <select name="sq_ut" id="sq_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="true" required="">
      <?php            
        $query0_1="select id_squadra, 
        concat(cod_squadra, ' - ', desc_squadra) as descr 
        from elem.squadre s order by desc_squadra ;";
        $result0_1 = pg_prepare($conn_sit, "query0_1", $query0_1); 
        $result0_1 = pg_execute($conn_sit, "query0_1", array()); 
        while($r0_1 = pg_fetch_assoc($result0_1)) { 
            //$valore=  $r2['id_via']. ";".$r2['desvia'];            
      ?>
        <option name="sq_ut" 
        <?php 
          if ($_POST["sq_gc"]==$r0_1['id_squadra']){
            echo ' selected ';
          } 
        ?>
        value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
      <?php } }?>
    </select>
  </div>
</div>

 <div class="row g-3 align-items-center">
  <div id="comuni" class="form-group col-md-12" style="display: none !important;">
    <?php
    if ($id_servizio_sit=="" and $cdr=='f'){
      $query_comuni="SELECT 
      cu.id_comune,
      cu.id_ut,
      c.descr_comune,
      COALESCE(pc.competenza, 0) AS competenza
      FROM topo.comuni_ut cu
      JOIN topo.comuni c 
          ON c.id_comune = cu.id_comune 
      LEFT JOIN anagrafe_percorsi.percorsi_comuni pc 
          ON pc.id_comune = cu.id_comune
          AND pc.cod_percorso = $1
      WHERE cu.id_ut = $2
      ORDER BY c.descr_comune;";
      $resultC = pg_prepare($conn_sit, "queryC", $query_comuni);
      $resultC = pg_execute($conn_sit, "queryC", array($codice_percorso, $_POST["gc"]));  
      $count_result = pg_num_rows($resultC);
      //echo $count_result."<br>"; 
      //echo $codice_percorso."<br>";
      //echo $_POST["gc"]."<br>";

      while($rC = pg_fetch_assoc($resultC)) { 
        ?>
          <div class="comuni-row">
            <label for="inlineCheckbox1"><?php echo $rC['descr_comune']?></label>
            <?php
              if($count_result == 1) {
                // Se c'è un solo comune, lo seleziono automaticamente e metto la checkbox disabled
                //devo però passare il valore dell'id_comune con un input hidden perchè il disabled non passa il valor in post
                echo '<input type="hidden" name="comuni[]" value="' . $rC['id_comune'] . '">';
              }
            ?>
            <input class="comuni-check" type="checkbox" style="border-color:darkgrey;" name="comuni[]" id="comune_<?php echo $rC['id_comune']?>" value="<?php echo $rC['id_comune']?>" <?php 
            if ($count_result == 1){
                echo "checked disabled";
            } elseif ($count_result > 1 and $rC['competenza'] > 0 ){
                echo "checked";
            } ?>>
            <input type="number" class="percent-input" name="percentuali[<?= $rC['id_comune'] ?>]" placeholder="%" min="0" max="100" <?php
              if ($count_result == 1) {
                // Se c'è un solo comune, imposto il valore 100 e metto l'input readonly
                //altrimenti lo metto disabled in modo che venga attivato solo se viene checcata la checbox corrispondente
                  echo 'value="100" readonly';
              } elseif ($count_result > 1 and $rC['competenza'] > 0 ){
                  echo 'value="'.$rC['competenza'].'"';
              }
              else {
                  echo 'disabled';
              }
            ?>>
          </div>
      <?php } 
      }?>

  </div>
 </div>

<hr>

<h4>Sede Operativa (rimessa) se presente</h4>
<div class="row g-3 align-items-center">

<div class="form-group  col-md-6">
  <label for="rim">Rimessa: <?php echo $_POST["rimessa"]; ?></label> 
  <select name="rim" id="rim" class="selectpicker show-tick form-control" data-live-search="false" >
  <?php if (!$_POST["rimessa"]){?>
    <option name="rim" value="">Nessuna rimessa selezionata</option>
  <?php }else{
    $query0="SELECT id_ut, descrizione from topo.ut 
    where id_ut = $1;";
    $result0 = pg_prepare($conn_sit, "query0", $query0);
    $result0 = pg_execute($conn_sit, "query0", array($_POST["rimessa"]));
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
  <?php }} ?>

  </select>            
</div>

<div class="form-group  col-md-6">
  <label for="sq_rim">Squadra rimessa:</label> 
  <?php if (!$_POST["rimessa"]){?>
    <select name="sq_rim" id="sq_rim" class="selectpicker show-tick form-control"  data-size="5"  data-live-search="false">
      <option name="sq_rim" value="">Nessuna rimessa selezionata</option>
    </select>
  <?php } else{?>
    <select name="sq_rim" id="sq_rim" class="selectpicker show-tick form-control"  data-size="5"  data-live-search="true">
  <?php
  $query0_1="select id_squadra, 
  concat(cod_squadra, ' - ', desc_squadra) as descr 
  from elem.squadre s order by desc_squadra ;";

  $result0_1 = pg_prepare($conn_sit, "query0_1", $query0_1);
  $result0_1 = pg_execute($conn_sit, "query0_1", array());  
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
  <?php } }?>

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
  $result2 = pg_query($conn_sit, $query2);
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
      if (finOra <= '06'){
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
      if (finOra <= '06'){
        document.getElementById('refDay').style.display = "block";
        console.log('il turno è notturno. Inizia alle '+ iniOra + ' e finisce alle '+ finOra)
      }else{
        document.getElementById('refDay').style.display = "none";
        console.log('il turno NON è notturno.')
      }
    }
    window.addEventListener('DOMContentLoaded', aggiornaRefDay);

  function showDestination(){
    tipo = document.getElementById("tipo_descr");
    if(tipo.dataset.cdr === 't'){
      console.log('è cdr')
      document.getElementById('destinazioni').style.display = "grid";
      document.getElementById('destinazioni').style.gridTemplateColumns = "50% 50%";
      //divdest.appendChild(divdescr);
      /*document.getElementById('rimessa').style.display = "block";
      document.getElementById('sqrimessa').style.display = "block";*/
    } else{
      console.log('NON è cdr')
      document.getElementById('destinazioni').style.display = "none";
      /*document.getElementById('rimessa').style.display = "none";
      document.getElementById('sqrimessa').style.display = "none";*/
    }
  }
  window.addEventListener('DOMContentLoaded', showDestination);

  form = document.getElementById("newversione");
  form.addEventListener("submit", function(e) {

    // prende tutte le checkbox checked
    let checked = document.querySelectorAll("input[name='destinazioni[]']:checked");
    tipo = document.getElementById("tipo_descr");
    // verifico se la lunghezza è 0 vuol dire che non hanno selezionato nulla
    if (checked.length === 0 && tipo.dataset.cdr ==='t') {
        e.preventDefault(); // blocca invio form
        alert("E' necessario selezionare almeno una destinazione.");
        return false;
    }

    if(id_sit==""){
      console.log("percorso SENZA id sit");
      if(cdr=="f"){
        // prende tutte le checkbox checked
        let checkedC = document.querySelectorAll("input[name='comuni[]']:checked");
        // verifico se la lunghezza è 0 vuol dire che non hanno selezionato nulla
        if (checkedC.length === 0) {
            e.preventDefault(); // blocca invio form
            alert("E' necessario selezionare almeno un comune avendo scelto un'UT che copre più comuni.");
            return false;
        }

        let comuni_checked = document.querySelectorAll(".percent-input:not([disabled])");
        let somma = 0;
        comuni_checked.forEach(cc => {
            somma += parseFloat(cc.value || 0);
        });

        // se vuoi permettere decimali, usa un controllo con tolleranza:
        if (somma != 100) {
            e.preventDefault();
            alert("La somma delle percentuali inserite per i comuni selezionati deve essere ESATTAMENTE 100. Somma attuale: " + somma);
        }
      }
    }
});

 document.addEventListener("change", function(e) {
    if (e.target.classList.contains("comuni-check")) {

        //trovo il div padre della checkbox
        let div_check = e.target.closest(".comuni-row") || e.target.parentElement;
        console.log(div_check);
      
        // cerca l'input test nel div padre
        let inputPercent = div_check.querySelector(".percent-input");
        if (!inputPercent) {
            // fallback: niente input trovato (debug)
            console.warn("Percent input non trovato per la checkbox", e.target);
            return;
        }

        if (e.target.checked) {
            inputPercent.disabled = false;
            // mette il puntatore del mouse sull'input corrispondente
            inputPercent.focus();
        } else {
            inputPercent.disabled = true;
            inputPercent.value = "";
        }
    }
});

window.addEventListener('DOMContentLoaded', function() {
    // Se ci sono checkbox già selezionate al caricamento della pagina, abilita gli input percentuali corrispondenti
    id_sit = "<?php echo $id_servizio_sit;?>";
    cdr = "<?php echo $cdr;?>";
    if(id_sit==""){
      console.log("percorso SENZA id sit");
      if(cdr=="f"){
        console.log("NON è un CDR");
        document.getElementById('comuni').style.display = "";
      }
    } else {
      console.log("percorso CON id sit, non devo fare nulla");
    }
});

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