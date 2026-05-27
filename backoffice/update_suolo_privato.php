<?php
session_start();
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;

$id_piazzola = $_POST["id_piazzola"];
//echo 'piazzola: '.$id_piazzola."<br>";


$suolo_privato = $_POST['privato'];
//echo 'suolo_privato: '.$suolo_privato."<br>";


//update elem.piazzole

$update_piazzola = "UPDATE elem.piazzole
SET suolo_privato=$1, modificata_da=$2, data_ultima_modifica=CURRENT_TIMESTAMP
WHERE id_piazzola=$3;";

$result_piazzola = pg_prepare($conn_sit, "update_piazzola", $update_piazzola);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_piazzola = pg_execute($conn_sit, "update_piazzola", array($suolo_privato, $_SESSION['username'], $id_piazzola)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su insert piazzola<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


if ($res_ok==0){
    echo 'Suolo privato aggiornato correttamente';
} else {
    echo $res_ok.'ERRORE nell\'aggiornamento del suolo privato';
}

?>