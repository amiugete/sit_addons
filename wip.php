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



?>


<div class="container">
  <div class="row">
    <h1 style="text-align:center;">PAGINA IN COSTRUZIONE</h1>
    <img src="./img/wip.png">
  </div>
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