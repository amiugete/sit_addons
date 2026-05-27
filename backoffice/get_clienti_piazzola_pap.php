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


$id_piazzola = $_GET['id_piazzola'];

$query_clienti = "select distinct id_macro_categoria, 
descrizione
from elem.elementi_privati 
where id_elemento in (
	select id_elemento from elem.elementi where id_piazzola = $1
)";

$result_clienti = pg_prepare($conn_sit, "select_clienti", $query_clienti);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_clienti = pg_execute($conn_sit, "select_clienti", array($id_piazzola));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_clienti)) {

    $clienti[] = $row;
}

header('Content-Type: application/json');

echo json_encode($clienti);

?>