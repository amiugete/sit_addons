<?php
require_once '../session.php';
#require('../validate_input.php');


require_once '../conn_ok.php';



$id_rifiuto = $_GET['id_rifiuto'];

//echo "id_rifiuto: " . $id_rifiuto . "<br>";


$query_tipo_elemento = "select tipo_elemento, nome, manutenzione from elem.tipi_elemento
where tipo_rifiuto = $1 and in_piazzola = 1
order by nome";

$result_tipo_elemento = pg_prepare($conn_sit, "select_tipo_elemento", $query_tipo_elemento);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_tipo_elemento = pg_execute($conn_sit, "select_tipo_elemento", array($id_rifiuto));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_tipo_elemento)) {

    $tipo_elemento[] = $row;
}

header('Content-Type: application/json');

echo json_encode($tipo_elemento);

?>