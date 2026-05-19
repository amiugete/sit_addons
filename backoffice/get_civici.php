<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']){
    if ($_SESSION['test']==1) {
        require_once ('../conn_test.php');
    } else {
        require_once ('../conn.php');
    }
} else {
    echo 'Sessione scaduta. Si prega di ricaricare la pagina per proseguire';
    exit();
}


$id_via = $_GET['id_via'];


$query_civ = "select id, cod_strada, testo, cod_civico, numero
from geo.v_civici where cod_strada = $1
order by numero::int, colore::int";

$result_civ = pg_prepare($conn_sit, "select_civ", $query_civ);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_civ = pg_execute($conn_sit, "select_civ", array($id_via));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_civ)) {

    $civ[] = $row;
}

header('Content-Type: application/json');

echo json_encode($civ);

?>