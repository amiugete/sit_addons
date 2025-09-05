<?php
session_start();
//require_once('./check_utente.php');

// Faccio il controllo su SIT

$query_role="SELECT  su.id_user, sr.id_role, sr.\"name\" as \"role\",
coalesce(suse.esternalizzati, 'f') as esternalizzati, 
coalesce(suse.sovrariempimenti, 'f') as sovrariempimenti, 
coalesce(suse.sovrariempimenti_admin, 'f') as sovrariempimenti_admin, 
coalesce(suse.coge, 'f') as coge
FROM util.sys_users su
join util.sys_roles sr on sr.id_role = su.id_role  
left join etl.sys_users_addons suse on suse.id_user = su.id_user 
where su.\"name\" ilike $1 and su.id_user>0;";
$result_n = pg_prepare($conn, "my_query_navbar1", $query_role);
$result_n = pg_execute($conn, "my_query_navbar1", array($_SESSION['username']));

$check_SIT=0;
while($r = pg_fetch_assoc($result_n)) {
  $role_SIT=$r['role'];
  $id_role_SIT=(int)$r['id_role'];
  //$id_user_SIT=$r['id_user'];
  $_SESSION['id_user']=$r['id_user'];
  $check_esternalizzati=$r['esternalizzati'];
  $check_sovr=$r['sovrariempimenti'];
  $check_sovr_admin=$r['sovrariempimenti_admin'];
  $check_coge=$r['coge'];
  $check_SIT=1;
}
//echo "<script type='text/javascript'>alert('$check_SIT');</script>";


if ($check_SIT==0){
  if ($check_modal!=1){
  redirect('login.php');
  exit(0);
  } else {
    echo 'Problema autenticazione';
  }
}

$check_edit_piazzola=0;

$check_edit=0; # edit dei percorsi 

$check_superedit=0; # permessi privilegiati


$ruoli_edit_piazzola=array('USER', 'UT', 'IT', 'ADMIN', 'SUPERUSER');
$ruoli_edit=array('UT', 'IT', 'ADMIN', 'SUPERUSER');
$ruoli_superedit=array('IT','ADMIN', 'SUPERUSER');

if (in_array($role_SIT, $ruoli_edit_piazzola)) {
  $check_edit_piazzola=1;
}

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
<h3>  <a class="navbar-brand link-light" href="./index.php">
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
        <?php if ($id_role_SIT >= 0) { ?>
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
          Reportistica
          </a>
          <ul class="dropdown-menu" id="navbarDropdown3" aria-labelledby="navbarDropdown3">
            <li><a class="dropdown-item" href="#">Reportistica avanzata &raquo; </a>
            <ul class="submenu dropdown-menu">
              <li><a class="dropdown-item" href="./consuntivazione_ekovision.php">Report consuntivazione Ekovision</a></li>
              <li><a class="dropdown-item" href="./report_indicatori_arera.php">Report indicatori ARERA (uso interno)</a></li>
              <li><a class="dropdown-item" href="./report_contenitori_bilaterali.php">Report contenitori bilaterali</a></li>
            </ul>
            <?php if ($check_coge == 't') { ?>
            <li><a class="dropdown-item" href="#">Reportistica COGE &raquo; </a>
            <ul class="submenu dropdown-menu">
              <a class="dropdown-item" href="./esportazione_driver_ekovision.php">Report driver ekovision (esportazione)</a>
            </ul>
            <?php } ?>
              <li><a class="dropdown-item" href="#">Report dati in tempo reale da totem (Raccolta)&raquo; </a>
              <ul class="submenu dropdown-menu">
                <li><a class="dropdown-item" href="./report_totem_percorsi.php"> Dettaglio per percorso </a></li>
                <li><a class="dropdown-item" href="./report_totem_piazzola.php"> Dettaglio per piazzole </a></li>
          
        
              </ul>
          
            </li>
            <!--a class="dropdown-item" href="http://amiupostgres/SIT/downloadTemplateImport()">Template per import</a-->
            </ul>
            
        <?php } ?>
        <?php if ($check_esternalizzati=='t') { ?>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown6">
          Ditte terze
          </a>
            <div class="dropdown-menu" id="navbarDropdown6" aria-labelledby="navbarDropdown6">

              <a class="dropdown-item" href="./targhe_ditte_terze.php">Targhe ditte terze</a>
              <a class="dropdown-item" href="./report_fascia_oraria_esecuzione.php">Report fascia oraria consuntivazione</a>
            </div>
          </li>

          <?php } ?>
        <?php if ($check_edit_piazzola > 0) { ?>
        <!--li id="link_pc2" class="nav-item">
          <a class="nav-link" href="./report_contenitori.php"> Report contenitori bilaterali</a>
        </li-->
        <?php if ($check_sovr == 't') { ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown5">
          Sovrariempimenti
          </a>
          <div class="dropdown-menu" id="navbarDropdown5" aria-labelledby="navbarDropdown5">
          
            <?php if ($check_edit == 1) { ?>
              <!--span class="disable-links"><a class="dropdown-item" href="#">Import dati per verifiche</a></span-->
              <a class="dropdown-item" href="./piazzola_sovr.php">Compilazione dati</a>
              <a class="dropdown-item" href="./report_piazzole_sovr.php">Report piazzole da ispezionare</a>
            <?php } ?>
            <a class="dropdown-item" href="./export_sovr.php">Export report sovrariempimenti</a>
            <?php if ($check_sovr_admin == 't') { ?>
            <a class="dropdown-item" href="./delete_ispezioni_sovr.php">Rimozione sovrariempimenti (solo superuser)</a>
            <?php } ?>

          </div>
        </li>
        <?php } ?>
        <?php } ?>
        <script type="text/javascript">
          function closeWindow() {

              // Open the new window 
              // with the URL replacing the
              // current page using the
              // _self value
              let new_window =
                  open(location, '_self');

              // Close this window
              new_window.close();

              return false;
          }
      </script>
        <li id="link_pc2" class="nav-item">
          <!--a class="nav-link" target="SIT" href="<?php echo $url_sit?>"> Torna a SIT</a-->
          <a class="nav-link" target="SIT" title="Chiudere funzionalità avanzate" href="#" onclick="return closeWindow();"> Chiudi e torna a SIT</a>
        </li>
        <!--li class="nav-item">
          <a class="nav-link" href="./ordini.php"> Modifica percorsi</a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="./chiusura.php">Chiusura interventi</a>
        </li-->
        
        
      </ul>
      
      <!--div class="collapse navbar-collapse flex-grow-1 text-right" id="myNavbar">
        <ul class="navbar-nav ms-auto flex-nowrap"-->
        <span class="navbar-light">
        <!--li class="nav-item dropdown"-->
        <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" 
        aria-expanded="false" aria-controls="navbarDropdown4">

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

            ?>)
          </a>
          <?php

          if (isset($filter_totem)){
            $filter_totem_ok= " join topo.ut u1 on u1.id_ut = cmu1.id_uo_sit where u1.utilizza_totem = true ";
          } else{
            $filter_totem_ok= " "; 
          }


          $query_utente="select su.\"name\", su.email, 
          concat(sr.name, ' - ', sr.description) as ruolo, 
          case
            when min(suu.id_ut) = -1 then 'Tutte le UT/Rimesse'
            else string_agg(u.descrizione, ', ') 
          end uts, 
          case
            when min(suu.id_ut) = -1 then (select string_agg(distinct id_uo::text, ', ') from anagrafe_percorsi.cons_mapping_uo cmu1 ".$filter_totem_ok.")
            else string_agg(cmu.id_uo::text, ', ') 
          end id_uos
          from util.sys_users su 
          join util.sys_roles sr on sr.id_role = su.id_role 
          left join util.sys_users_ut suu on suu.id_user = su.id_user 
          left join topo.ut u on u.id_ut = suu.id_ut 
          left join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut
          where  su.\"name\" ilike $1 and su.id_user > 0
          group by su.\"name\", su.email, sr.name, sr.description";

          //echo $query_utente;
          $result1 = pg_prepare($conn, "my_queryUser", $query_utente);
          $result1 = pg_execute($conn, "my_queryUser", array($_SESSION['username']));


          while($r1 = pg_fetch_assoc($result1)) {
            $mail_user=$r1['email'];
            $profilo=$r1['ruolo'];
            
            $uts=$r1['uts'];
            $uos=$r1['id_uos'];
            $_SESSION['id_uos']=$r1['id_uos'];

          }

          ?>
        <div class="dropdown-menu" style="left: auto" id="navbarDropdown4" aria-labelledby="navbarDropdown4">
          <ul>
            <li><b>Mail: </b><?php echo $mail_user?></li>
            <li><b>Profilo: </b><?php echo $profilo?></li>
            <li><b>UT/Rimesse: </b><?php echo $uts?></li>
            <hr>
            <li><b>Funzionalità ad accesso profilato: </b>
            <ul>
              <li><b>Servizi esternalizzati: </b>
              <?php if ($check_esternalizzati=='t'){?>
                <i class="fa-solid fa-check" style="color: #00c217;"></i>
              <?php } else {?>
                <i class="fa-solid fa-xmark" style="color: #ff0000;"></i>
              <?php } ?>
            </li>
              <li><b>Sovrariempimenti: </b>
              <?php if ($check_sovr=='t'){?>
                <i class="fa-solid fa-check" style="color: #00c217;"></i>
              <?php } else {?>
                <i class="fa-solid fa-xmark" style="color: #ff0000;"></i>
              <?php } ?>
              <?php if ($check_sovr_admin=='t'){?>
                <i class="fa-solid fa-user-tie" style="color: #00c217;"></i>
              <?php } ?>
            </li>
              <li><b>Controllo gestione: </b>
              <?php if ($check_coge=='t'){?>
                <i class="fa-solid fa-check" style="color: #00c217;"></i>
              <?php } else {?>
                <i class="fa-solid fa-xmark" style="color: #ff0000;"></i>
              <?php } ?>
            </li>
            </ul>
          </ul>
        <hr>
          In caso di modifiche fare scrivere dal proprio responsabile a assterritorio@amiu.genova.it    
        </div>


        <!--/li-->
        </span>

    </div>
  </div>
</nav>
<?php 
if ($_SESSION['test']==1) {
?> <div> <?php

$conto_underscore=count(explode("_", basename($_SERVER['PHP_SELF'])));

  //if (count(explode("_", basename($_SERVER['PHP_SELF'])))> 1) { 
    if (explode("_", basename($_SERVER['PHP_SELF']))[$conto_underscore-1] == 'sovr.php'){ 
      ?>
      <h4><i class="fa-solid fa-triangle-exclamation"></i> Ambiente di TEST!</h4>
      <?php
    } else {
      ?>
      <h4><i class="fa-solid fa-triangle-exclamation"></i> Ambiente di TEST ma dati in esercizio!</h4>
      <?php
    }
  /*} else {

?>
 <h4><i class="fa-solid fa-triangle-exclamation"></i> Ambiente di TEST ma dati in esercizio!</h4>
<?php
  } */

  
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

<script>
  document.addEventListener("DOMContentLoaded", function(){
// make it as accordion for smaller screens
if (window.innerWidth < 992) {

  // close all inner dropdowns when parent is closed
  document.querySelectorAll('.navbar .dropdown').forEach(function(everydropdown){
    everydropdown.addEventListener('hidden.bs.dropdown', function () {
      // after dropdown is hidden, then find all submenus
        this.querySelectorAll('.submenu').forEach(function(everysubmenu){
          // hide every submenu as well
          everysubmenu.style.display = 'none';
        });
    })
  });

  document.querySelectorAll('.dropdown-menu a').forEach(function(element){
    element.addEventListener('click', function (e) {
        let nextEl = this.nextElementSibling;
        if(nextEl && nextEl.classList.contains('submenu')) {	
          // prevent opening link if link needs to open dropdown
          e.preventDefault();
          if(nextEl.style.display == 'block'){
            nextEl.style.display = 'none';
          } else {
            nextEl.style.display = 'block';
          }

        }
    });
  })
}
// end if innerWidth
}); 
// 
</script>

<?php 
$in_manutenzione=0;

if ($in_manutenzione==1){
  ?>
  <body>
    <div class="container" style="text-align:center;">
        <h1>Sito in Manutenzione</h1>
        <h4>Stiamo effettuando alcune operazioni di aggiornamento. Torna a trovarci tra poco!</h4>
    </div>
</body>

  <?php 
  die();

}


?> 