<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

/*foreach ($_POST as $key => $value) {
    echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
}*/


$id_uo = intval($_POST['id_uo']);

$targa = $_POST['targa'];;


//echo $id_uo.'<br>';
//echo $targa.'<br>';


// aggiungi elemento
$update_targa="UPDATE etl.mezzi_ditte_terze 
SET in_uso='f', data_aggiornamento=now()
WHERE id_uo=$1 AND targa=$2 ";

$result_add = pg_prepare($conn, "update_targa", $update_targa);
//echo  pg_last_error($conn_sovr);
if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
}

$result_add = pg_execute($conn, "update_targa", array($id_uo, $targa));
if (pg_last_error($conn)){
    echo pg_last_error($conn).'<br>';
    $res_ok=$res_ok+1;
}


if ($res_ok==0) {
    echo 'Mezzo con targa '.$targa .' aggiornato';
    http_response_code(200);
} else {
    http_response_code(400);
    echo '<br>ERRORE<br>';
}

?>