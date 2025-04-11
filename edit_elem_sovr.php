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


$id_elemento= $_GET['id'];
$num_freq= $_GET['num_freq'];

$select_elementi="
    select 
te.tipo_elemento,
te.descrizione as tipologia_elemento, 
e.matricola, 
e.tag
from elem.elementi e 
join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
where e.id_elemento = $1
    ";
    $result_ee = pg_prepare($conn_sovr, "my_query_e", $select_elementi);
    $result_ee = pg_execute($conn_sovr, "my_query_e", array($id_elemento));
    $status1= pg_result_status($result_ee);
    ?>
    
    <?php
    while($re = pg_fetch_assoc($result_ee)) {

?>
<form name="edit_elemento" id="form_edit_elemento"  autocomplete="off">


<input type="hidden" id="id_elemento" name="id_elemento" value="<?php echo $id_elemento;?>">


<input type="hidden" id="num_freq" name="num_freq" value="<?php echo $num_freq;?>">


<?php 
if ($num_freq > 0){
?>
<div class="mb-3">
<div title="perc" class="input-group-text">Elemento associato a percorso</div>
</div>
<?php
$query_tipologie="select es.tipo_elemento, 
  te.nome
  FROM elem.tipi_elemento te 
  join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto 
  join (select distinct es.tipo_elemento 
		from elem.elementi_aste_percorso eap 
		join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
		join elem.percorsi p on p.id_percorso = ap.id_percorso
		left join elem.elementi_servizio es on es.id_servizio = p.id_servizio
		where eap.id_elemento = $1
		and p.id_categoria_uso in (3,6)
		) es  on es.tipo_elemento = te.tipo_elemento
  where te.tipo_rifiuto is not null 
  --and tipo_elemento = 1
  and in_piazzola = 1 
  and tipologia_elemento not in ('T', 'I', 'N')
  order by  te.volume";
} else {
?>
<div class="mb-3">
<div title="perc" class="input-group-text">Elemento privo di associazione a percorsi</div>
</div>
<?php
  $query_tipologie=" select te.tipo_elemento, 
  te.nome
  from  elem.tipi_elemento te 
  where te.tipo_rifiuto in (select tr.tipo_rifiuto
   FROM elem.tipi_elemento te 
  join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
  join elem.elementi e on e.tipo_elemento = te.tipo_elemento
  where e.id_elemento = $1 )
  and tipologia_elemento not in ('T', 'I', 'N')
  order by te.volume";
}

  $result_tt = pg_prepare($conn_sovr, "my_query_tipologie", $query_tipologie);
  $result_tt = pg_execute($conn_sovr, "my_query_tipologie", array($id_elemento));
  $status1= pg_result_status($result_tt);
  ?>
  
 

  <div class="mb-3">
  <label for="tipo_elemento_tt" class="form-label">Tipo <?php //echo $re['tipologia_elemento'];?></label>

    <select class="selectpicker input-group-btn show-tick form-control open" 
    data-live-search="true" name="tipo_elemento_tt" id="tipo_elemento_tt"  required="">
    <?php
      while($rtt = pg_fetch_assoc($result_tt)) {
    ?>
      
        <option name="tipo_elemento_tt" value="<?php echo $rtt['tipo_elemento'];?>" 
        <?php 
        if ($rtt['tipo_elemento']== $re['tipo_elemento']) {
            echo 'selected="" ';
          }
        ?>  
        >
          <?php echo $rtt['nome']; ?>
        </option>
    <?php
      }
    ?>
  </select>
<small>E' possibile cambiare tipologia elemento sulla base di quelle compatibili con il tipo servizio</small>
</div>




<div class="mb-3">
<div title="Id_elemento" class="input-group-text"><?php echo 'Id elemento SIT:'.$id_elemento;?></div>
</div>


<div class="mb-3">
  <label for="matr" class="form-label">Matr</label>
    <input type="text"  class="form-control" id="matr" name="matr" value="<?php echo $re['matricola'];?>">
  </>
</div>



<div class="mb-3">
<label for="tag" class="form-label">Tag</label>
    <input type="text"  class="form-control" id="tag" name="tag" value="<?php echo $re['tag'];?>">
</div>

<hr>
<div class="mb-3">   
    <button type="submit" class="btn btn-info">
    <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
    </button>
    </div> 
  </form>
<p><div id="results_edit_elemento"></div></p>
            <script> 
            $(document).ready(function () {                 
                $('#form_edit_elemento').submit(function (event) { 
                    console.log('Bottone form edit_elemento cliccato e finito qua');
                    event.preventDefault();                  
                    var formData = $(this).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/update_elemento_sovr.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            $("#results_edit_elemento").html(response).fadeIn("slow");
                            if (response.startsWith("9999")){
                              location.reload();
                            }
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>


<script type="text/javascript">

$(function () {
	$('select').selectpicker();
});
</script>


<?php
}
?> 
</html>