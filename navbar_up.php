<?php
//require_once('./check_utente.php');

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
  if ($check_modal!=1){
  redirect('login.php');
  //exit;
  } else {
    echo 'Problema autenticazione';
  }
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


if ($check_modal!=1){

?>
<div class="navbar-header">
<div id="intestazione" class="banner"> <div id="banner-image">
<h3>  <a class="navbar-brand link-light" href="#">
    <img class="pull-left" src="img\amiu_small_white.png" alt="SIT" width="85px">
    <span>Sistema Informativo Territoriale - Funzionalità avanzate 
    <?php 
    if ($_SESSION['test']==1) {
       echo "(ambiente di TEST)";
    }
    ?>


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
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!--li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li-->
        <?php if ($id_role_SIT > 0) { ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown1">
          Anagrafica percorsi / servizi
          </a>
          <div class="dropdown-menu" id="navbarDropdown1" aria-labelledby="navbarDropdown1">
            <a class="dropdown-item" href="./percorsi.php">Elenco servizi UO/SIT</a>
            <?php if ($check_superedit == 1) { ?>
            <a class="dropdown-item" href="./nuovo_percorso.php">Nuovo servizio</a>
            <?php } ?>
            <!--a class="dropdown-item" href="http://amiupostgres/SIT/downloadTemplateImport()">Template per import</a-->
          </div>
        </li>
        <?php } ?>
        <?php if ($check_superedit == 1) { ?>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown2">
          Funzionalità amministratori SIT
          </a>
          <div class="dropdown-menu" id="navbarDropdown2" aria-labelledby="navbarDropdown2">
            <a class="dropdown-item" href="./update_elementi.php">Forzare update elementi</a>
            <!--a class="dropdown-item" href="./nuovo_percorso.php">Nuovo servizio</a-->
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
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown3">
          Reportistica avanzata
          </a>
          <div class="dropdown-menu" id="navbarDropdown3" aria-labelledby="navbarDropdown3">
            <?php if ($check_superedit == 1) { ?>
              <a class="dropdown-item" href="./consuntivazione_ekovision.php">Report consuntivazione Ekovision</a>
            <?php } ?>
            <a class="dropdown-item" href="./report_contenitori_bilaterali.php">Report contenitori bilaterali</a>
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
<?php 
// TEST e DEBUG COOKIES
/*
foreach ($_COOKIE as $key=>$val)
{
  echo $key.' is '.$val."<br>\n";
}
echo ' session username= '. $_SESSION['username']."<br>";
echo ' session expire= '. $_SESSION['expire']."<br>";
echo 'time = ' .time()."<br>";*/
?>
</div>
</div>
<hr>
<?php } // check_modal 
 } ?>