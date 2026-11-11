<?php
require_once '../session.php';
#require('../validate_input.php');



require_once '../conn_ok.php';


$query_materiali = "select * from elem.tipi_materiale order by id_materiale";

$result_materiali = pg_prepare($conn_sit, "select_materiali", $query_materiali);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_materiali = pg_execute($conn_sit, "select_materiali", array());
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_materiali)) {

    $materiali[] = $row;
}

header('Content-Type: application/json');

echo json_encode($materiali);

?>