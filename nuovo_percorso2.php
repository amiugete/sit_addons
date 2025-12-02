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

//STAGIONALITà
$stag =  $_POST['stag'];
if($stag!=''){
  $switchOng = str_pad($_POST['switchong'], 2, "0", STR_PAD_LEFT);
  $switchOnm = str_pad($_POST['switchonm'], 2, "0", STR_PAD_LEFT);
  $switchON = $switchOng.$switchOnm;
  $switchOffg = str_pad($_POST['switchoffg'], 2, "0", STR_PAD_LEFT);
  $switchOffm = str_pad($_POST['switchoffm'], 2, "0", STR_PAD_LEFT);
  $switchOFF = $switchOffg.$switchOffm;
}else{
  $stag = null;
  $switchON = null;
  $switchOFF = null;
}

//echo 'stagione è '.$stag.'<br>';
//echo 'ON è '.$switchON.'<br>';
//echo 'OFF è '.$switchOFF.'<br>';

//exit();

$tipo = $_POST['tipo'];

$query1="select id, descrizione, lpad(codice_servizio,4,'0') as cod_start, 
id_servizio_uo, coalesce(id_servizio_sit,0) as id_servizio_sit, ut_obbligatoria, cdr
from anagrafe_percorsi.anagrafe_tipo at2 
where id=$1;";
$result1 = pg_prepare($conn, "query1", $query1);
$result1 = pg_execute($conn, "query1", array($tipo));  
//echo $query1;    
while($r1 = pg_fetch_assoc($result1)) { 
  $cod_start=$r1['cod_start'];
  $id_servizio_uo=$r1['id_servizio_uo'];
  $id_servizio_sit=$r1['id_servizio_sit'];
  $ut_obbligatoria=$r1['ut_obbligatoria'];
  $cdr=$r1['cdr'];
}

/* // Query UO per definire la seconda parte del codice percorso
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
*/

// NUOVA QUERY SU sit PER DEFINIRE LA SECONDA PARTE DEL CODICE PERCORSO 
$queryid2sit="SELECT LPAD(
  CAST(COALESCE(MAX(CAST(SUBSTRING(cod_percorso FROM 5 FOR 4) AS INTEGER)), 0) + 1 AS VARCHAR), 4, '0' ) AS id
  FROM anagrafe_percorsi.elenco_percorsi
  WHERE LENGTH(cod_percorso) = 10
  AND SUBSTRING(cod_percorso FROM 1 FOR 4) = $1
  AND SUBSTRING(cod_percorso FROM 5 FOR 4) ~ '^[0-9]+$';
";
$resultid2 = pg_prepare($conn_sit, "queryid2", $queryid2sit);
$resultid2 = pg_execute($conn_sit, "queryid2", array($cod_start));  
//echo $query1;    
while($rid2 = pg_fetch_assoc($resultid2)) { 
  $id_percorso_parte2=$rid2['id'];
}

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


if ($_POST['check_ref_day']){
  $check_refday = intval($_POST['check_ref_day']);
} else {
  $check_refday = 0;
}
//echo "refday:".$check_refday."<br>";

//exit();
// FREQUENZA


require('backoffice/decodifica_frequenza.php');


$freq_sit='';

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

  if ($freq_sit==''){
    echo $frequenza_binaria;
    echo '<br>Bisogna aggiungere una nuova frequenza su SIT';
    echo '<br> IStruzioni:';
    ?>

    <ul>
      <li>andare sui percorsi del SIT (no funzionalità avanzate)</li>
      <li>nella colonna categoria filtrare fra quelli in progetto</li>
      <li>selezionare il percorso con codice = <i>test_freq</i> e descrizione <i>TEST x frequenza</i>
      impostare la frequenza che si vuole creare e salvare</li>
      <li>a quel punto entro 5 minuti dovrebbe arrivare una mail generata dallo script frequenze.py con le istruzioni per aggiornare Ekovision e la UO 
        e qua sulle funzionalità avanzate dovrebbe esssere possibile creare tranquillamente il percorso</li>
    </ul>

  Poi con calma sarebbe da sistemare qua sopra e creare la frequenza in automatico replicando quanto fatto dallo script frequenze.py .. 
    <?php
    exit();
  }


// VECCHIA MODALITA
/*$freq = $_POST['freq'];

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
*/


if (!empty($_POST['destinazioni'])) {
  $destinazioni= $_POST['destinazioni'];
    
} else {
    $destinazioni = [""];
}

if (!empty($_POST['id_sit'])) {
  $id_sit = $_POST['id_sit'];    
} else {
  $id_sit = "";
}

$freq_sett= $_POST['freq_sett'];

$data_att = $_POST['data_att'];
$data_disatt = $_POST['data_disatt'];

$codice_percorso=$cod_start.''.$id_percorso_parte2.''.$id_percorso_turno;
?>
<h3>Testata percorso</h3>
<ul><?php
//echo '<li><b>Nuovo codice</b>: '.$codice_percorso.'</li>';
echo '<li><b>Id percorso</b>: '.$id_percorso_parte2.'</li>';
echo '<li><b>Descrizione</b>: '.$desc.'</li>';
echo '<li><b>Frequenza</b>: '.$descrizione_long;
if ($freq_sett=='T'){
  echo '';
 } else if ($freq_sett=='P') {
  echo ' - Solo settimane <b>Pari</b>';
 } else if ($freq_sett=='D') {
 echo ' - Solo settimane <b>Dispari</b>';
}
echo '</li>';
echo '<li><b>Durata</b>: '.$durata.'</li>';
echo '<li><b>Data attivazione</b>: '.$data_att.'</li>';
echo '<li><b>Data disattivazione</b>: '.$data_disatt.'</li>';
echo '<li><b>CDR</b>: '.$cdr.'</li>';
//echo '<li><b>Destinazioni: </b>: ';
//foreach ($destinazioni as $dest) {
//        echo $dest . " ";
//    }
//echo '</li>';
?></ul><?php
//exit();

?>


<hr>


<form id="newpercorso2" name="bilat" method="post" autocomplete="off" action="./backoffice/nuovo_percorso3.php">

<input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $codice_percorso;?>">
<input type="hidden" id="desc" name="desc" value="<?php echo $desc;?>">
<input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
<input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
<input type="hidden" id="$freq_sett" name="$freq_sett" value="<?php echo $freq_sett;?>">
<input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
<input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
<input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
<input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
<input type="hidden" id="refday" name="refday" value="<?php echo $check_refday;?>">
<input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo;?>">
<input type="hidden" id="stag" name="stag" value="<?php echo $stag;?>">
<input type="hidden" id="switchon" name="switchon" value="<?php echo $switchON;?>">
<input type="hidden" id="switchoff" name="switchoff" value="<?php echo $switchOFF;?>">
<input type="hidden" id="data_att" name="data_att" value="<?php echo $data_att;?>">
<input type="hidden" id="data_disatt" name="data_disatt" value="<?php echo $data_disatt;?>">
<input type="hidden" id="destinazioni" name="destinazioni" value="<?php echo implode(',', $destinazioni); ?>">


<?php
if ($id_servizio_sit){
?>
<div class="form-check">
  <input class="form-check-input" type="checkbox" value="1" id="check_SIT" name="check_SIT" checked>
  <label class="form-check-label" for="check_SIT">
    Creo il percorso anche su SIT <i class="fa-solid fa-map-location-dot"></i> (disabilitare nel caso in cui si voglia clonare il percorso su SIT ad esempio per un cambio servizio)
  </label>
</div>
<?php
}
?>

<div class="form-check">
  <input class="form-check-input" type="checkbox" value="t" id="check_EKO" name="check_EKO" <?php if ($check_superedit == 0) {echo 'disabled';} ?> checked>
  <label class="form-check-label" for="check_EKO">
    Il percorso verrà automaticamente trasferito anche a Ekovision <i class="fa-solid fa-circle-nodes"></i> (per disabilitare scrivere a </i>assterritorio</i>)
  </label>
</div>

<hr>

<h4>Gruppo di coordinamento o UT Responsabile</h4>
<!--small id="uts" class="form-text text-muted"> Deve sempre esserci un Gruppo di Coordinamento. <b>Per tutti i servizi di raccolta deve essere una Unità territoriale.</b> 
    Nel caso di servizi della sola rimessa (es. Ganci) è la rimessa stessa.</small-->



<div class="row g-3 align-items-center" style="margin-bottom: 1%;">

<div class="form-group  col-md-6">
  <label for="ut">UT:</label> <font color="red">*</font>
  <select name="ut" id="ut" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" required=""  onchange="showSedeOperativa(this)">
    <option name="ut" value="">Seleziona il Gruppo di Coordinamento (UT)</option>
  <?php            
  /*$query1="select id_ut, descrizione from topo.ut 
  where id_zona not in (5) 
  order by descrizione ;";*/


  //Tengo anche le rimesse (poi faccio un controllo dopo)

  $query0="select distinct id_ut, u.descrizione, za.rimessa from topo.ut  u
  join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut
  join topo.zone_amiu za on za.id_zona = u.id_zona
  where (u.data_disattivazione is null or u.data_disattivazione> now())";
  if($ut_obbligatoria=='t'){
    $query0=$query0."and id_ut not in (155, 154)";
  }
  $query1=$query0."order by descrizione ;";

  //echo $query1;
  $result1 = pg_query($conn, $query1);
  //echo $query1;    
  while($r1 = pg_fetch_assoc($result1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="ut" value="<?php echo $r1['id_ut']?>" data-rimessa="<?php echo $r1['rimessa']?>"><?php echo $r1['descrizione'] ?></option>
  <?php } ?>

  </select>  
            
</div>

<div class="form-group  col-md-6">
  <label for="sq_ut">Squadra UT:</label> <font color="red">*</font>
  <select name="sq_ut" id="sq_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="true" required="">
    <option name="sq_ut" value="">Seleziona la squadra del Gruppo di Coordinamento</option>
  <?php            
  $query0_1="select id_squadra, 
  concat(cod_squadra, ' - ', desc_squadra) as descr 
  from elem.squadre s order by desc_squadra ;";

  $result0_1 = pg_prepare($conn, "query0_1", $query0_1);
  $result0_1 = pg_execute($conn, "query0_1", array()); 
  while($r0_1 = pg_fetch_assoc($result0_1)) { 
      //$valore=  $r2['id_via']. ";".$r2['desvia'];            
  ?>
            
        <option name="sq_ut" value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
  <?php } ?>

  </select>            
</div>

</div>


 <div class="row g-3 align-items-center">
  <div id="comuni" class="form-group col-md-12">

  </div>
 </div>

<h4 id="so" style="display: none;">Sede Operativa (rimessa) se presente</h4>
<div id="sedeOperativa" class="row g-3 align-items-center" >

<div id="rimessa" class="form-group  col-md-6" style="display: none;">
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

<div id="sqrimessa" class="form-group  col-md-6" style="display: none;">
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



<hr>
<div class="row g-3 align-items-center">


<!--div class="form-group  col-md-4">
  <label for="data_inizio" >Data attivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_att" id="js-date" type="text" class="form-control" value="" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
</div>

<div class="form-group  col-md-4">

  <label for="data_inizio" >Data disattivazione (GG/MM/AAAA) </label><font color="red">*</font>
      <input name="data_disatt" id="js-date1"  type="text" class="form-control" value="31/12/2099" required="">
      <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
      </div>
</div-->
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


<!--div class="alert alert-error collapse" role="alert" id="alert_UT">
  <span>
  <p>ATTENZIONE. Non posso selezionare la rimessa se il gruppo di coordinamento è già la rimessa!</p>
  </span>
</div-->
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
/*
$('#js-date').datepicker({
    format: 'dd/mm/yyyy',
    startDate: '+1d', 
    language:'it' 
});

$('#js-date1').datepicker({
    format: 'dd/mm/yyyy', 
    language:'it'
});
*/

function showSedeOperativa(val){
    console.log(val.value)
    console.log(val)
    ut = document.getElementById("ut");
    opt = ut.selectedOptions[0];
    console.log(opt.dataset)
    if(opt.dataset.rimessa === 't'){
      document.getElementById('so').style.display = "block";
      document.getElementById('rimessa').style.display = "block";
      document.getElementById('sqrimessa').style.display = "block";
    } else{
      document.getElementById('so').style.display = "none";
      document.getElementById('rimessa').style.display = "none";
      document.getElementById('sqrimessa').style.display = "none";
    }
    id_sit = "<?php echo $id_sit;?>";
    cdr = "<?php echo $cdr;?>";
    if(id_sit==""){
      console.log("percorso SENZA id sit");
      if(cdr=="f"){
        console.log("NON è un CDR");
        fetch("backoffice/select_comuni_percorso.php?ut=" + val.value)
        .then(response => response.text())
        .then(html => {
            document.getElementById("comuni").innerHTML = html;
        })
        .catch(error => {
            console.error("Errore fetch:", error);
        });
      }
    } else {
      console.log("percorso CON id sit, non devo fare nulla");
    }
    
  }

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

  form = document.getElementById("newpercorso2");
  form.addEventListener("submit", function(e) {
    console.log("id sit al submit è: "+ id_sit);
    if(id_sit==""){
      console.log("percorso SENZA id sit");
      if(cdr=="f"){
        // prende tutte le checkbox checked
        let checked = document.querySelectorAll("input[name='comuni[]']:checked");
        // verifico se la lunghezza è 0 vuol dire che non hanno selezionato nulla
        if (checked.length === 0) {
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


/*var bk1 = document.getElementById("rim").value;
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
}*/

</script>



</body>

</html>