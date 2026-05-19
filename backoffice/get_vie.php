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


$id_comune = $_GET['id_comune'];


$query_vie = "select id_via, nome, id_comune from topo.vie
where id_comune = $1
order by nome";

$result_vie = pg_prepare($conn_sit, "select_vie", $query_vie);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_vie = pg_execute($conn_sit, "select_vie", array($id_comune));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_vie)) {

    $vie[] = $row;
}

header('Content-Type: application/json');

echo json_encode($vie);

?>