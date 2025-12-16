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

    <title>Update vie</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}

$via=$_GET["id_via"] ?? '';
?> 

</head>

<body>



<?php 
require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ($check_superedit==1) {
  redirect('no_permessi.php');
  //exit;
}
?>




<div class="container">

Da questa pagina è possibile visualizzare i dati sulle vie e in quanto Super User modificarli su DB. 

<hr>
<!--form id="update_elem" method="post" autocomplete="off" action="" onsubmit="return clickButton();"-->
<?php if ($via ==''){?>
<form id="view_via" method="GET">
<div class="row g-3 align-items-center">
  <div class="form-group col-md-6">
    <label for="id_elem"> Id via </label> <font color="red">*</font>
    <input type="text" name="id_via" id="id_via" class="form-control" required="">
  </div>
</div>
<br>
<div class="row g-3 align-items-center">
  <?php if ($check_edit==1){?>
    <div class="form-group col-md-6">
      <button type="submit" class="btn btn-info">
      <i class="fa-solid fa-arrows-rotate"></i> Procedi
      </button>
    </div>
  <?php }?>
</div>
</form>
<?php } else  {?>
<p><div id="results">
  <h3> 
    Id_via <?php echo $via;?>
  </h3>
  <div class="row">
    <div class="col">
      <h4>SIT</h4>
  <?php
  $select_sit="
	select id_via, nome,
	v.via_ordinata, 
	v.id_comune, 
	data_ultima_modifica, 
	c.descr_comune, c.prefisso_utenti, 
	case 
		when (select 1 from elem.aste a where a.id_via = v.id_via)=1 then 1
		else 0
	end sit
	from topo.vie v 
	left join topo.comuni c on c.id_comune = v.id_comune 
	where v.id_via = $1";

  
    
  
    
  // la preparazione fuori dal ciclo
  $result = pg_prepare($conn_sit, "select_sit", $select_sit);
  //echo  pg_last_error($conn_sovr);
  if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
  }

  $result = pg_execute($conn_sit, "select_sit", array($via));
  
  if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
  }
    
    
  while($r = pg_fetch_assoc($result)) {
    //echo '<b>Nome via</b>: '. $r["nome"];
    ?>
    <b>Nome via</b>
    <!--form class="row row-cols-lg-auto g-3 align-items-center" name="form_desc" method="post" autocomplete="off" action="./backoffice/update_descrizione.php"-->
    <form class="row row-cols-lg-auto g-3 align-items-center" name="form_desc" id="form_desc" autocomplete="off">
    <input type="hidden" id="id_via" name="id_via" value="<?php echo $via;?>">
    <input type="hidden" id="pref" name="pref" value="<?php echo $r["prefisso_utenti"];?>">
    <div class="col-auto">
    <div class="input-group">
    <input type="text" name="nome" id="nome" maxlength="60" class="form-control" value="<?php echo $r["nome"];?>" required="">
    </div>
  </div>
    <div class="col-auto">
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
                        url: 'backoffice/update_descr_via.php', 
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
    echo '<br><b>Via ordinata</b>: '. $r["via_ordinata"];

    // 1. trim + sostituzione di spazi multipli / tab / newline con un solo spazio
    $stringa = trim(preg_replace('/\s+/', ' ', $r['nome']));

    $stringa =  mb_strtoupper(
        mb_convert_encoding($stringa, 'UTF-8', 'auto'),
        'UTF-8'
    );
    // 2. separa le parole
    $parole = explode(' ', $stringa);

    // 3. se c'è più di una parola, sposta la prima in fondo
    if (count($parole) > 1) {
        $prima = array_shift($parole);
        $parole[] = $prima;
    }

    // 4. ricomponi e metti in maiuscolo gestendo accenti
    $risultato = implode(' ', $parole);
   
    echo '<br><b>Via ordinata test</b>: '. $risultato;

    if($risultato === $r['via_ordinata']){
      echo '<i class="fa-solid fa-check-double"></i>';
    } else {
      echo '<i title="Differenze fra none e via ordinata" class="fa-solid fa-triangle-exclamation"></i>';
    }
    echo '<br><b>Id comune</b>: '. $r["id_comune"];
    echo '<br><b>Comune</b>: '. $r["descr_comune"];
    echo '<br><b>Prefisso comune</b>: '. $r["prefisso_utenti"];
    echo '<br>';
    if(intval($r["sit"]) == 1){
      echo '<i style="color:green" class="fa-solid fa-map-location"></i> Aste su SIT';
    } else {
      echo '<i style="color:red" title="Aste non presenti su SIT" class="fa-regular fa-map"></i> Aste non presenti su SIT ';
    }
  }

  ?>
  </div>
  <div class="col">
  <h4>STRADE su PEOR</h4>

 <?php
 
  $select_uo="select 
CODICE_VIA, 
nome1, 
nome2,
cap, 
comune, 
CODICE_VIA_PRIMARIO,
nome_breve, 
nome_stampa
from strade.STRADE WHERE CODICE_VIA  = :p1";
 

  $result_s = oci_parse($oraconn, $select_uo);
  # passo i parametri
  oci_bind_by_name($result_s, ':p1', $via);

  $ris=oci_execute($result_s);
  //echo $ris;

  while (($r = oci_fetch_assoc($result_s)) != false) {
    echo '<b>Nome 1</b>: '. $r["NOME1"];
    echo '<br><b>Nome 2</b>: '. $r["NOME2"];
    echo '<br><b>CAP</b>: '. $r["CAP"];;
    echo '<br><b>Comune</b>: '. $r["COMUNE"];
    echo '<br><b>Codice via primario</b>: '. $r["CODICE_VIA_PRIMARIO"];
    echo '<br><b>Nome breve</b>: '. $r["NOME_BREVE"];
    echo '<br><b>Nome stampa</b>: '. $r["NOME_STAMPA"];
  }

oci_free_statement($result_s);
oci_close($oraconn);
    
  ?>


  </div>
  </div>

</div></p>

<?php } ?>


<script> 
        $(document).ready(function () {                 
            $('#update_elem').submit(function (event) { 
                console.log('Bottone cliccato e finito qua');
                event.preventDefault();                 
                //var form = document.getElementById('update_elem'); 
                //var formData = new FormData(form); 
                var formData = $(this).serialize();
                console.log(formData);
                $.ajax({ 
                    url: 'backoffice/update_elemento.php', 
                    method: 'POST', 
                    data: formData, 
                    //processData: true, 
                    //contentType: false, 
                    success: function (response) {                       
                        //alert('Your form has been sent successfully.'); 
                        console.log(response);
                        $("#results").html(response).fadeIn("slow");
                    }, 
                    error: function (jqXHR, textStatus, errorThrown) {                        
                        alert('Your form was not sent successfully.'); 
                        console.error(errorThrown); 
                    } 
                }); 
            }); 
        }); 
    </script>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>