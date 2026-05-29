<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
#require('../validate_input.php');


require_once '../conn_ok.php';



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