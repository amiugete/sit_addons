<?php
require_once '../session.php';
#require('../validate_input.php');


require_once '../conn_ok.php';



$tipo_elemento = $_GET['tipo_elemento'];
$id_piazzola = $_GET['id_piazzola'];
$dettagli = [];

$query_elemento_piazzola = "select max(e.percent_riempimento) as percent_riempimento, max(e.freq_stimata)::integer as freq_stimata, manutenzione, 1 as exists
from elem.elementi e
join elem.tipi_elemento t on e.tipo_elemento = t.tipo_elemento
where e.id_piazzola = $1 and e.tipo_elemento = $2
group by manutenzione";

$result_elemento_piazzola = pg_prepare($conn_sit, "select_elemento_piazzola", $query_elemento_piazzola);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_elemento_piazzola = pg_execute($conn_sit, "select_elemento_piazzola", array($id_piazzola, $tipo_elemento));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}  

while($row = pg_fetch_assoc($result_elemento_piazzola)) {

    $dettagli[] = $row;
}

//echo "id_rifiuto: " . $id_rifiuto . "<br>";

if (count($dettagli) == 0) {
    
    $query_tipo_elemento = "select percent_riempimento, freq_stimata::integer, manutenzione, 0 as exists 
    from elem.tipi_elemento
    where tipo_elemento = $1";

    $result_tipo_elemento = pg_prepare($conn_sit, "select_tipo_elemento", $query_tipo_elemento);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    $result_tipo_elemento = pg_execute($conn_sit, "select_tipo_elemento", array($tipo_elemento));
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    while($row = pg_fetch_assoc($result_tipo_elemento)) {

        $dettagli[] = $row;
    }
}

header('Content-Type: application/json');

echo json_encode($dettagli);

?>