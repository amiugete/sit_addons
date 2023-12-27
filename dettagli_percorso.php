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

$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];
?>


<div class="container">
<?php
$query_testata = 'select ep.cod_percorso, 
ep.descrizione, t.cod_turno, ep.durata, fo.descrizione_long
from anagrafe_percorsi.elenco_percorsi ep 
join elem.turni t on t.id_turno = ep.id_turno
join etl.frequenze_ok fo on fo.cod_frequenza = ep.freq_testata
where cod_percorso = $1 and versione_testata  = $2';
$result = pg_prepare($conn, "query_testata", $query_testata);
$result = pg_execute($conn, "query_testata", array($cod_percorso, $versione));  

//echo $cod_percorso . '<br>';
//echo $versione . '<br>';
?>
<h3> Testata percorso </h3>
<?php
echo '<ul>';
while($r = pg_fetch_assoc($result)) {
  echo '<li><b> Codice percorso </b>'.$r["cod_percorso"].'</li>';
  echo '<li><b> Versione percorso </b>'.$versione.'</li>';
  echo '<li><b> Descrizione </b>'.$r["descrizione"].'</li>'; 
  echo '<li><b> Turno </b>'.$r["cod_turno"].'</li>';
  echo '<li><b> Durata </b>'.$r["durata"].'</li>';
  echo '<li><b> Frequenza </b>'.$r["descrizione_long"].'</li>';
}
echo '</ul>';

# percorso su SIT
$query_sit = 'select p.id_percorso
from elem.percorsi p 
where cod_percorso = $1 and versione = (select max(versione)
    from elem.percorsi p1 where cod_percorso = $1)';
$result_sit = pg_prepare($conn, "query_sit", $query_sit);
$result_sit = pg_execute($conn, "query_sit", array($cod_percorso));  


while($r_s = pg_fetch_assoc($result_sit)) {
  echo '<a class="btn btn-primary" href="'.$url_sit.'/#!/percorsi/percorso-details/?idPercorso='.$r_s["id_percorso"].'"> <i class="fa-solid fa-map-location-dot"></i> Percorso su SIT</a>';
}

?>
<hr>
<h3> Risorse umane e risorse tecniche </h3>
<?php
$query0="select 
u.descrizione as ut,
pu.id_squadra, 
pu.cdaog3,
pu.responsabile, 
pu.solo_visualizzazione, 
pu.rimessa, 
pu.data_attivazione, 
pu.data_disattivazione
from anagrafe_percorsi.percorsi_ut pu 
join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
join topo.ut u on u.id_ut = cmu.id_uo_sit 
where cod_percorso = $1";

// RIMESSA / SEDE OPERATIVA
$query_rimessa=$query0 ." and rimessa = 'S'";
$result1 = pg_prepare($conn, "query_rimessa", $query_rimessa);
$result1 = pg_execute($conn, "query_rimessa", array($cod_percorso));
echo '<ul>';
while($r1 = pg_fetch_assoc($result1)) {
  echo '<h4><li><b> Sede operativa </b>'.$r1["ut"].'</li></h4>';
  echo '<li><b> Id Squadra </b>'.$r1["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li><b> Mezzo </b>'.$r1["cdaog3"].'</li>';
  echo '<li><b> Responsabile </b>'.$r1["responsabile"].'</li>';
  echo '<li><b> Solo visualizzazione </b>'.$r1["solo_visualizzazione"].'</li>';
}
echo '</ul>';


$query_ut=$query0 ." and rimessa = 'N'";
$result2 = pg_prepare($conn, "query_ut", $query_ut);
$result2 = pg_execute($conn, "query_ut", array($cod_percorso));
//echo '<hr>';
echo '<ul>';
while($r2 = pg_fetch_assoc($result2)) {
  echo '<h4><li><b> Gruppo di coordinamento</b> '.$r2["ut"].'</li></h4>';
  echo '<li><b> Id Squadra </b>'.$r2["id_squadra"].'</li>'; 

  // inserire composizione squadra con funzioncina da recuperare anche sotto 


  echo '<li><b> Mezzo </b>'.$r2["cdaog3"].'</li>';
  echo '<li><b> Responsabile </b>'.$r2["responsabile"].'</li>';
  echo '<li><b> Solo visualizzazione </b>'.$r2["solo_visualizzazione"].'</li>';
}
echo '</ul>';
?>








</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>