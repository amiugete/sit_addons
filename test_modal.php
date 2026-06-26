<?php
//session_set_cookie_params($lifetime);
require_once './session.php';

$check_modal = 1;

require_once('./req.php');

the_page_title();

require_once './conn_ok.php';



require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}


$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];

?>

<div>TEST MODAL</div> 


<?php
//require_once('req_bottom.php');
//require_once('./footer.php');
?>



<script>

$('#js-date').datepicker({
    format: 'dd/mm/yyyy',
    startDate: '+1d', 
    language:'it' 
});

</script>
