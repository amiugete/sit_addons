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


/*$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];
*/

// verifico se c'è già una versione successiva
;
$query_check_versione="select ep.cod_percorso from anagrafe_percorsi.elenco_percorsi ep
where ep.cod_percorso = $1 and ep.versione_testata > $2";
$result0 = pg_prepare($conn, "query_check_versione", $query_check_versione);
$result0 = pg_execute($conn, "query_check_versione", array($cod_percorso, intval($versione)));
while($r0 = pg_fetch_assoc($result0)) {
  $check_versione_successiva=1;
}
?>


<div class="container">

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

</script>

</body>

</html>