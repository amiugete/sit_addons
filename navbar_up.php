<?php
require_once('./check_utente.php');

// Faccio il controllo su SIT

$query_role='SELECT  su.id_user, sr.id_role, sr."name" as "role" FROM util.sys_users su
join util.sys_roles sr on sr.id_role = su.id_role  
where su."name" ilike $1;';
$result_n = pg_prepare($conn, "my_query_navbar1", $query_role);
$result_n = pg_execute($conn, "my_query_navbar1", array($_SESSION['username']));

$check_SIT=0;
while($r = pg_fetch_assoc($result_n)) {
  $role_SIT=$r['role'];
  $id_role_SIT=(int)$r['id_role'];
  //$id_user_SIT=$r['id_user'];
  $_SESSION['id_user']=$r['id_user'];
  $check_SIT=1;
}
//echo "<script type='text/javascript'>alert('$check_SIT');</script>";

if ($check_SIT==0){
  redirect('login.php');
  //exit;
}

$check_edit=0;
$check_superedit=0;
$ruoli_edit=array('UT', 'IT', 'ADMIN', 'SUPERUSER');
$ruoli_superedit=array('IT','ADMIN', 'SUPERUSER');

if (in_array($role_SIT, $ruoli_edit)) {
  $check_edit=1;
}

if (in_array($role_SIT, $ruoli_superedit)) {
  $check_superedit=1;
}
?>

<div id="intestazione" class="banner"> <div id="banner-image">
<h3>  <a class="navbar-brand link-light" href="#">
    <img class="pull-left" src="img\amiu_small_white.png" alt="SIT" width="85px">
    <span>SIT - Add ons <?php ?>


    </span> 
  </a> 
</h3>
</div> 
</div>
<nav class="navbar navbar-sticky-top navbar-expand-lg navbar-light" id="main_navbar">
  <div class="container-fluid">
    <!--a class="navbar-brand" href="#">
    <img class="pull-left" src="img\amiu_small_white.png" alt="SIT" width="85px">
    </a-->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!--li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li-->
        <?php if ($id_role_SIT > 0) { ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Anagrafica percorsi / servizi
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="./percorsi.php">Elenco servizi UO/SIT</a>
            <?php if ($check_superedit == 1) { ?>
            <a class="dropdown-item" href="./nuovo_percorso.php">Nuovo servizio</a>
            <?php } ?>
            <!--a class="dropdown-item" href="http://amiupostgres/SIT/downloadTemplateImport()">Template per import</a-->
          </div>
        </li>
        <?php } ?>
      


        <?php if ($check_superedit == 1) { ?>
        <!--li class="nav-item">
          <a class="nav-link" href="./nuovo_percorso.php">Nuovo servizio</a>
        </li-->
        <?php
        } 
        if ($id_role_SIT > 0) { ?>
        <!--li class="nav-item">
          <a class="nav-link" href="./percorsi.php">Elenco servizi UO/SIT</a>
        </li-->
        <?php } ?>
        <?php if ($id_role_SIT >=0) { ?>
        <!--li id="link_pc2" class="nav-item">
          <a class="nav-link" href="./report_contenitori.php"> Report contenitori bilaterali</a>
        </li-->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Reportistica avanzata
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="./report_contenitori.php">Report contenitori bilaterali</a>
            <!--a class="dropdown-item" href="http://amiupostgres/SIT/downloadTemplateImport()">Template per import</a-->
          </div>
        </li>

        <li id="link_pc2" class="nav-item">
          <a class="nav-link" target="SIT" href="<?php echo $url_sit?>"> Torna a SIT</a>
        </li>
        <!--li class="nav-item">
          <a class="nav-link" href="./ordini.php"> Modifica percorsi</a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="./chiusura.php">Chiusura interventi</a>
        </li-->
        <?php } ?>
        
      </ul>
      
      <!--div class="collapse navbar-collapse flex-grow-1 text-right" id="myNavbar">
        <ul class="navbar-nav ms-auto flex-nowrap"-->
        <span class="navbar-light">
          <i class="fas fa-user"></i> Connesso come <?php echo $_SESSION['username'];?> (
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

            ?>
            )
        </span>

    </div>
  </div>
</nav>
<?php 
if ($_SESSION['test']==1) {
?>
<div> <h4><i class="fa-solid fa-triangle-exclamation"></i> Ambiente di TEST ma dati in esercizio!</h4>
</div>
<hr>
<?php } ?>