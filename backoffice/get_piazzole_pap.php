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
$id_civico = $_GET['civico'];


$query_piazzola = "with piazzole as(
select vp.id_piazzola, vp.riferimento, p.via,
vp.numero_civico, vp.numero_civico||coalesce(vp.lettera_civico,'')||coalesce(vp.colore_civico,'') as civico_testo,  
vp.id_asta, p.elementi, p.is_pap, p.geoloc 
from elem.piazzole vp 
join elem.v_piazzole_dwh p on p.id_piazzola  = vp.id_piazzola 
where vp.id_asta in (select id_asta from elem.aste tv where tv.id_via = $1 )
)
select * from piazzole 
where civico_testo ilike $2";

$result_piazzola = pg_prepare($conn_sit, "select_piazzola", $query_piazzola);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_piazzola = pg_execute($conn_sit, "select_piazzola", array($id_via, $id_civico));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_piazzola)) {

    $civ[] = $row;
}

header('Content-Type: application/json');

echo json_encode($civ);

?>