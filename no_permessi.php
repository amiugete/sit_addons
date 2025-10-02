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



    <title>SIT AddOns - Utente sprovvisto dei permessi </title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 


<style>
#successo { display: none; }
</style>


</head>

<body>

<?php require_once('./navbar_up.php');


$name=dirname(__FILE__);
?>


<div class="container">
<hr>
<h3><i class="fa-solid fa-user-slash"></i> L'utente non disponde dei permessi per visualizzare la pagina </h3>
<hr>
<div  id="nuova" class="row">



</div> <!-- chiudo row -->


<hr>



</div>

<?php
require_once('req_bottom.php');
require_once('./mappa_georef.php');
require_once('./footer.php');
?>



<script type="text/javascript">

// questo non fa funzionare il select cascade
/*$(function () {
	$('select').selectpicker();
});*/

</script>



</body>

</html>