<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


// TURNO
//echo $_POST['id_elem']."<br>";
//$id_elemento = intval($_POST['id_elem']);
//echo $id_elemento."<br>";
$id_elemento = $_POST['id_elem'];

$query_update="update elem.piazzole set data_ultima_modifica = now() where id_piazzola in ( 
	select distinct id_piazzola from elem.elementi e where id_elemento = $1
)";



$result_update = pg_prepare($conn, "query_update", $query_update);
//echo  pg_last_error($conn);
if (pg_last_error($conn)){
    echo '<i class="fa-solid fa-triangle-exclamation"></i> Problema connessione DB  - '.pg_last_error($conn);
}
$result_update = pg_execute($conn, "query_update", array($id_elemento));
//echo"<br>";
if (pg_result_error($result_update)){
    echo '<i class="fa-solid fa-triangle-exclamation"></i> Problema aggiornamento elemento - '.pg_result_error($result_update);
}

// numero di righe aggiornate (lo uso sotto)
//echo pg_affected_rows($result_update);


$status = pg_result_status($result_update);
//echo $status;
if ($status ==1 and pg_affected_rows($result_update)==1){
    echo '<i class="fa-solid fa-check"></i> Forzato aggiornamento elemento con id ' . $id_elemento;
} else if ($status ==1 and pg_affected_rows($result_update)==0){
    echo '<i class="fa-solid fa-triangle-exclamation"></i> Non esiste elemento con id ' . $id_elemento;
} else {
    echo '<i class="fa-solid fa-triangle-exclamation"></i> Problema aggiornamento elemento';
}
?>