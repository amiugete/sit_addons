<?php
require_once '../session.php';
#require('../validate_input.php');



require_once '../conn_ok.php';



$tipo_rifiuto = $_POST['tipo_rifiuto'];
$tipo_raccolta = $_POST['tipo_raccolta'];



$query_tipi = "select te.tipo_elemento, upper(te.descrizione) as descrizione from elem.tipi_elemento te
  join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
  where te.tipo_rifiuto = $1 and te2.tipo_raccolta = $2";

$result_tipi = pg_prepare($conn_sit, "select_tipi", $query_tipi);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_tipi = pg_execute($conn_sit, "select_tipi", array($tipo_rifiuto, $tipo_raccolta));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_tipi)) {

    $tipi[] = $row;
}

header('Content-Type: application/json');

echo json_encode($tipi);

?>