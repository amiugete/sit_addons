<?php
//session_set_cookie_params($lifetime);
session_start();
?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Elenco piazzole verificate</title>
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
//echo $id_role_SIT;
//exit;
if ((int)$id_role_SIT == 0) {
  redirect('no_permessi.php');
  //exit;
}

?>


<div class="container">








</div>

<?php

require_once('req_bottom.php');
require('./footer.php');
?>



<script type="text/javascript">

$(function () {
	$('select').selectpicker();
});







</script>



</body>

</html>