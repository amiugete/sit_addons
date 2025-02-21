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

<h5>Buongiorno, sei connesso come <?php echo $_SESSION['username'];?><?php echo "" ?>(
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
    </h5>
    <hr>
    <!--h4>
    Sfoglia il menù in alto per accedere alle funzioni avanzate di SIT. 
    </h4-->
    <div class="row">
    <h3> Anagrafiche</h3>
    <div class="col-sm-4">
    <div class="card" >
      <div class="card-header">
        <h3> <i class="fa-solid fa-table-list"></i> Anagrafica percorsi</h3>
      </div>
      <ul class="list-group list-group-flush">
        <a class="list-group-item" href="./percorsi.php">Elenco servizi UO/SIT</a>
        <?php if ($check_superedit == 1) { ?>
            <a class="list-group-item" href="./nuovo_percorso.php">Nuovo servizio</a>
        <?php } ?>
      </ul>
    </div>
    </div>
    
    
    


    <?php if ($check_superedit == 1 OR $check_esternalizzati==1) { ?>
    <div class="col-sm-4">
    <div class="card" >
      <div class="card-header">
        <h3><i class="fa-solid fa-users-viewfinder"></i> Ditte terze</h3>
      </div>
      <ul class="list-group list-group-flush">
        <a class="list-group-item" href="./targhe_ditte_terze.php">Targhe ditte terze</a>
        <a class="list-group-item" href="./report_fascia_oraria_esecuzione.php">Report fascia oraria consuntivazione</a>
      </ul>
    </div>
    </div>
    <?php } ?>


    
    <!--img src="./img/graph-6249046_1280.png" class="img-fluid" alt="Responsive image"-->
    <!--div class="text-center">

    <img src="./img/graph-6249046_1280.png" class="rounded img-thumbnail" style="width:50%" alt="Responsive image">
    
    </div-->

</div>
<hr>
<div class="row">
<h3>Report</h3>

<div class="col-sm-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa-solid fa-chart-line"></i> Reportistica avanzata</h3>
      </div>
      <ul class="list-group list-group-flush">
        <a class="list-group-item" href="./consuntivazione_ekovision.php">Report consuntivazione Ekovision</a>
        <a class="list-group-item" href="./report_contenitori_bilaterali.php">Report contenitori bilaterali</a>
      </ul>
    </div>
    </div>

<div class="col-sm-4">
    <div class="card" >
      <div class="card-header">
        <h4><i class="fa-solid fa-tablet-screen-button"></i> Dati consuntivazione da totem</h4>
        <i class="fa-solid fa-hourglass-half"></i> Dati aggiornati ogni 5' da totem / backoffice consuntivazione
        <br><i class="fa-solid fa-clock-rotate-left"></i> Dati ultima settimana 
      </div>
      <ul class="list-group list-group-flush">
        <a class="list-group-item" href="./report_totem_percorsi.php">Report consuntivazione percorsi per UT </a>
        <a class="list-group-item" href="./report_totem_piazzola.php">Report consuntivazione piazzole per UT </a>
        <!--a class="list-group-item" href="./report_fascia_oraria_esecuzione.php">Report fascia oraria consuntivazione</a-->
      </ul>
    </div>
    </div>
    </div>
    <hr>
<div class="row">
<h3>Altre funzionalità</h3>
<?php if ($check_superedit == 1 OR $check_sovrariempimenti==1) { ?>
<div class="col-sm-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa-solid fa-user-secret"></i>Ispezioni sovrariempimenti</h3>
      </div>
      <ul class="list-group list-group-flush">
        <a class="list-group-item" href="./piazzola_sovr.php">Compilazione dati anno in corso</a>
        <a class="list-group-item" href="./report_piazzole_sovr.php">Report piazzole da ispezionare</a>
        <a class="list-group-item" href="./export_sovr.php">Export report sovrariempimenti</a>
        <?php if ($check_superedit == 1) { ?>
          <a class="list-group-item" href="./delete_ispezioni_sovr.php">Rimozione sovrariempimenti (solo superuser)</a>
        <?php } ?>
      </ul>
    </div>
    </div>
    <?php } ?>

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