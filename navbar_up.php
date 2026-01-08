<?php
session_start();
//require_once('./check_utente.php');

if ($_SESSION['test']==1) {
  //echo 'Ambiente di TEST attivo';
require_once ('./conn_test.php');
} else {
  //echo 'Ambiente di PRODUZIONE attivo';
  require_once ('./conn.php');
}

// Faccio il controllo su SIT (sempre produzione non test)

//echo "<script type='text/javascript'>alert('$check_SIT');</script>";
$check_user_ns="SELECT * from util_ns.sys_users where \"name\" ilike $1;";
$result_ns = pg_prepare($conn_sit, "my_query_navbar_ns", $check_user_ns);
$result_ns = pg_execute($conn_sit, "my_query_navbar_ns", array($_SESSION['username']));
if (pg_num_rows($result_ns) > 0) {
    // L'utente esiste già in util_ns, faccio update last_access
    $update_ns = "UPDATE util_ns.sys_users SET last_access = NOW()
        WHERE \"name\" ilike $1;";
    pg_prepare($conn_sit, "update_user_ns", $update_ns);
    pg_execute($conn_sit, "update_user_ns", array($_SESSION['username']));
}else{
  // L'utente non esiste in util_ns, verifico se invece esiste in util
  $check_user="SELECT * from util.sys_users where \"name\" ilike $1;";
  $result_u = pg_prepare($conn_sit, "my_query_u", $check_user);
  $result_u = pg_execute($conn_sit, "my_query_u", array($_SESSION['username']));
  if (pg_num_rows($result_u) > 0) {
    while($ru = pg_fetch_assoc($result_u)) {
      $domain_name=$ru['domain_name'];
      $user_name=$ru['name'];
      $id_role_SIT=$ru['id_role'];
      $id_user_SIT=$ru['id_user'];
      $email=$ru['email'];
    }
    // L'utente non esiste in util_ns ma in util si, faccio insert con dati di util 
    $insert_user_ns = "INSERT INTO util_ns.sys_users (domain_name, \"name\", id_role, last_access, id_user, email)
      VALUES($1, $2, $3, NOW(), $4, $5);";
    $result_user_ns = pg_prepare($conn_sit, "insert_user_ns", $insert_user_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_user_ns = pg_execute($conn_sit, "insert_user_ns", array($domain_name, $user_name, $id_role_SIT, $id_user_SIT, $email));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }

    $insert_comune_ns = "INSERT INTO util_ns.sys_users_comuni (id_user, id_comune) VALUES($1, -1);";
    $result_comune_ns = pg_prepare($conn_sit, "insert_comune_ns", $insert_comune_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_comune_ns = pg_execute($conn_sit, "insert_comune_ns", array($id_user_SIT));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }

    $insert_ut_ns = "INSERT INTO util_ns.sys_users_ut (id_user, id_ut) VALUES($1, -1);";
    $result_ut_ns = pg_prepare($conn_sit, "insert_ut_ns", $insert_ut_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_ut_ns = pg_execute($conn_sit, "insert_ut_ns", array($id_user_SIT));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
  }else{
    // L'utente non esiste nè in util_ns nè in util, faccio insert in entrambi
    // Insert in util_ns
    $insert_user_ns = "INSERT INTO util_ns.sys_users (domain_name, \"name\", id_role, last_access, id_user, email)
      VALUES('DSI', $1, 0, NOW(), (select max(id_user)+1 from util_ns.sys_users), '')
      RETURNING id_user;";
    $result_user_ns = pg_prepare($conn_sit, "insert_user_ns", $insert_user_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_user_ns = pg_execute($conn_sit, "insert_user_ns", array($_SESSION['username']));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $added_user = pg_fetch_assoc($result_user_ns);
    $id_added_user = $added_user['id_user'];

    $insert_comune_ns = "INSERT INTO util_ns.sys_users_comuni (id_user, id_comune) VALUES($1, -1);";
    $result_comune_ns = pg_prepare($conn_sit, "insert_comune_ns", $insert_comune_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_comune_ns = pg_execute($conn_sit, "insert_comune_ns", array($id_added_user));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }

    $insert_ut_ns = "INSERT INTO util_ns.sys_users_ut (id_user, id_ut) VALUES($1, -1);";
    $result_ut_ns = pg_prepare($conn_sit, "insert_ut_ns", $insert_ut_ns);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_ut_ns = pg_execute($conn_sit, "insert_ut_ns", array($id_added_user));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }

    // Insert in util
    $insert_user_util = "INSERT INTO util.sys_users (domain_name, \"name\", id_role, last_access, id_user, email)
      VALUES('DSI', $1, 0, NOW(), (select max(id_user)+1 from util.sys_users), '')
      RETURNING id_user;";
    $result_user_util = pg_prepare($conn_sit, "insert_user_util", $insert_user_util);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_user_util = pg_execute($conn_sit, "insert_user_util", array($_SESSION['username']));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $added_user_util = pg_fetch_assoc($result_user_util);
    $id_added_user_util = $added_user_util['id_user'];

    $insert_comune_util = "INSERT INTO util.sys_users_comuni (id_user, id_comune) VALUES($1, -1);";
    $result_comune_util = pg_prepare($conn_sit, "insert_comune_util", $insert_comune_util);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_comune_util = pg_execute($conn_sit, "insert_comune_util", array($id_added_user_util));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }

    $insert_ut_util = "INSERT INTO util.sys_users_ut (id_user, id_ut) VALUES($1, -1);";
    $result_ut_util = pg_prepare($conn_sit, "insert_ut_util", $insert_ut_util);
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
    $result_ut_util = pg_execute($conn_sit, "insert_ut_util", array($id_added_user_util));
    if (pg_last_error($conn_sit)){
      echo pg_last_error($conn_sit);
    }
  }
}

$query_role="SELECT  su.id_user, sr.id_role, sr.\"name\" as \"role\",
coalesce(suse.esternalizzati, 'f') as esternalizzati, 
coalesce(suse.sovrariempimenti, 'f') as sovrariempimenti, 
coalesce(suse.sovrariempimenti_admin, 'f') as sovrariempimenti_admin, 
coalesce(suse.coge, 'f') as coge,
coalesce(suse.utenze, 'f') as utenze
FROM util.sys_users su
join util.sys_roles sr on sr.id_role = su.id_role  
left join util_ns.sys_users_addons suse on suse.id_user = su.id_user 
where su.\"name\" ilike $1 and su.id_user>0;";
$result_n = pg_prepare($conn_sit, "my_query_navbar1", $query_role);
$result_n = pg_execute($conn_sit, "my_query_navbar1", array($_SESSION['username']));

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
  $check_utenze=$r['utenze'];
  $check_SIT=1;
}

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
          Anagrafica servizi
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
        <!--?php if ($check_superedit == 1) { ?>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown2">
          Funzionalità amministratori SIT
          </a>
          <div class="dropdown-menu" id="navbarDropdown2" aria-labelledby="navbarDropdown2">
            <a class="dropdown-item" href="./update_elementi.php">Forzare update elementi</a>
          </div>
        </li-->
        <!--?php } ?-->
              


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
              <!--li><a class="dropdown-item" href="./report_indicatori_arera.php">Report indicatori ARERA (uso interno)</a></li-->
              <li><a class="dropdown-item" href="./report_contenitori_bilaterali.php">Report contenitori bilaterali</a></li>
            </ul>
            <?php if ($check_edit == 1) { ?>
              <li><a class="dropdown-item" href="#">Reportistica ARERA &raquo; </a>
            <ul class="submenu dropdown-menu">
              <li><a class="dropdown-item" href="./report_indicatori_arera.php">Report raccolta e spazzamento</a></li>
              <li><a class="dropdown-item" href="./report_pin_arera.php">Report Pronto Intervento</a></li>
            </ul>
            <?php } ?>
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
              <li><a class="dropdown-item" href="#">Report pesi &raquo; </a>
            <ul class="submenu dropdown-menu">
              <li><a class="dropdown-item" href="./wip.php">Report dettaglio pesi per percorso</a></li>
              <li><a class="dropdown-item" href="./wip.php">Report dettaglio pesi per UT</a></li>
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
        <?php if ($check_utenze == 't') { ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown7">
          Estrazione utenze
          </a>
          <div class="dropdown-menu" id="navbarDropdown7" aria-labelledby="navbarDropdown7">
              <!--span class="disable-links"><a class="dropdown-item" href="#">Import dati per verifiche</a></span-->
              <a class="dropdown-item" href="./index_vie.php">Estrazione utenze per via</a>
              <a class="dropdown-item" href="./index_aree.php">Estrazione utenze per area</a>
            <!--a class="dropdown-item" href="./index_ecopunti.php">Estrazione utenze ecopunti</a-->

          </div>
        </li>
        <?php } ?>
        <?php if ($check_superedit == 1) { ?>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#"  role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="navbarDropdown2">
          Admin SIT
          </a>
          <div class="dropdown-menu" id="navbarDropdown2" aria-labelledby="navbarDropdown2">
            <a class="dropdown-item" href="./update_elementi.php">Forzare update elementi</a>
            <a class="dropdown-item" href="./update_vie.php">Modifica nome vie</a>
            <!--a class="dropdown-item" href="./nuovo_percorso.php">Nuovo servizio</a-->
          </div>
        </li>
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
          <a class="nav-link" target="SIT" title="Chiudere funzionalità avanzate" href="#" onclick="return closeWindow();"> Torna a SIT</a>
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

          <i class="fas fa-user"></i><?php echo $_SESSION['username'];?> (
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
          $result1 = pg_prepare($conn_sit, "my_queryUser", $query_utente);
          $result1 = pg_execute($conn_sit, "my_queryUser", array($_SESSION['username']));


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
            <li><b>Estrazione utenze: </b>
              <?php if ($check_utenze =='t'){?>
                <i class="fa-solid fa-check" style="color: #00c217;"></i>
              <?php } else {?>
                <i class="fa-solid fa-xmark" style="color: #ff0000;"></i>
              <?php } ?>
            </li>
            </ul>
          </ul>
        <hr>
          In caso di modifiche fare scrivere dal proprio responsabile a assterritorio@amiu.genova.it 
          <hr>
          <a class="dropdown-item" href="./logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>  
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