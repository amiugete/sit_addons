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



?>


<div class="container">

<h3>Buongiorno, sei connesso come <?php echo $_SESSION['username'];?><?php echo "" ?>(
            <?php 
              echo $role_SIT;
            if ($check_edit==0){
              echo '<i class="fa-regular fa-eye"></i>';
            } else {
              echo '<i class="fa-solid fa-pencil"></i>';
            }
            if ($check_superedit==1){
              echo '<i class="fa-solid fa-unlock-keyhole"></i>';
            }

            ?>). <br>
    </h3>
    <h4>
    Sfoglia il menù in alto per accedere alle funzioni avanzate di SIT. 
    </h4>
    <!--img src="./img/graph-6249046_1280.png" class="img-fluid" alt="Responsive image"-->
    <div class="text-center">

    <img src="./img/graph-6249046_1280.png" class="rounded img-thumbnail" style="width:50%" alt="Responsive image">
    
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