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


$select_elementi="
    select 
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
<form name="edit_elemento" id="form_edit_elemento" class="row row-cols-lg-auto g-3 align-items-center" autocomplete="off">


<input type="hidden" id="id_elemento" name="id_elemento" value="<?php echo $id_elemento;?>">

<div class="col-12">
<div title="Tipo" class="input-group-text"><?php echo $id_elemento;?></div>
</div>


<div class="col-12">
<div title="Tipo" class="input-group-text"><?php echo $re['tipologia_elemento'];?></div>
</div>




<div class="col-12">
  <div class="input-group">
    <div title="Dato opzionale" class="input-group-text">Matr</div>
    <input type="text"  class="form-control" id="matr" name="matr" value="<?php echo $re['matricola'];?>">
  </div>
</div>



<div class="col-12">
  <div class="input-group">
    <div title="Dato opzionale" class="input-group-text">Tag</div>
    <input type="text"  class="form-control" id="tag" name="tag" value="<?php echo $re['tag'];?>">
  </div>
</div>

<div class="col-12">
    
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
}
?> 
</html>