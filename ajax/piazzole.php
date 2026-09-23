<?php
require_once '../session.php';
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


require_once '../conn_ok.php';
//echo "OK";

$q=isset($_GET['q']) ? intval($_GET['q']) : null;
$id_comune=isset($_GET['id_comune']) ? intval($_GET['id_comune']) : null;
$id_quartiere = isset($_GET['id_quartiere']) ? intval($_GET['id_quartiere']) : null;
$id_municipio = isset($_GET['id_municipio']) ? intval($_GET['id_municipio']) : null;

$filter = "" ;

if(!$conn_sit) {
    die('Connessione fallita !<br />');
} else {

    if ($id_quartiere !== null) {
        $filter = " AND a.id_quartiere  = $id_quartiere";
    }
    if ($id_municipio !== null) {
        $filter = " AND a.id_municipio  = $id_municipio";
    }
    if ($id_comune !== null) {
        $filter = " AND v.id_comune  = $id_comune";
    }
    $query="SELECT p.id_piazzola as id, 
concat (p.id_piazzola, ' - ', v.nome, ', ', p.numero_civico, ' - ', p.riferimento ) as descrizione
from elem.piazzole p 
join  elem.aste a on a.id_asta = p.id_asta
join topo.vie v on v.id_via = a.id_via
where p.data_eliminazione is null and starts_with(p.id_piazzola::text, $1::text)";

 
    //echo $query0;
    //echo $uos;
    //echo "Sono qua";

    $query0 = $query." ".$filter . " order by 1" ;

    $result = pg_prepare($conn_sit, "query0", $query0);

    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 2";
    $result = pg_execute($conn_sit, "query0", array($q));  
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
       echo  pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    //echo "Sono qua 3";


    $rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //echo $r['piazzola'];
    }
            

    //echo "sono qua!";
    require_once "../tables/json_no_paginazione.php";



    exit(0);
}


?>