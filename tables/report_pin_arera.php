<?php
session_start();
#require('../validate_input.php');

header('Content-Type: application/json; charset=utf-8');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
 
    
/*$query0="select * from treg_gap.richieste r 
        where scopo ilike '%pronto intervento%' and sottoscopo not ilike 'Chiusura%' and r.rimosso_treg = 0 and r.data_ins = (
            select max(r1.data_ins) from treg_gap.richieste r1 
            where r1.scopo ilike '%pronto intervento%' and r1.sottoscopo not ilike 'Chiusura%' 
            and (r1.cod_ident_segn  = r.cod_ident_segn OR (r1.cod_ident_segn IS NULL AND r.cod_ident_segn IS NULL))
        )";*/

$query0="with causale as 
        (select cd.codice, cd.descrizione as descr_amiu,
        ca.descrizione as descr_arera,
        ca.id_treg
        from etl.cause_disserv cd
        join etl.causali_arera ca on ca.id = cd.id_causale_arera)
        select * from treg_gap.richieste r 
        left join causale on causale.codice = r.mot_rit
        where scopo ilike '%pronto intervento%' and sottoscopo not ilike 'Chiusura%' and r.rimosso_treg = 0 and r.data_ins = (
            select max(r1.data_ins) from treg_gap.richieste r1 
            where r1.scopo ilike '%pronto intervento%' and r1.sottoscopo not ilike 'Chiusura%' 
            and (r1.cod_ident_segn  = r.cod_ident_segn OR (r1.cod_ident_segn IS NULL AND r.cod_ident_segn IS NULL))
        )";

$filter='';

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 

$query= $query0.''. $filter.' order by data_telefonata';

$result = pg_prepare($conn, "my_query", $query);
    if (pg_last_error($conn)){
        echo pg_last_error($conn);
        exit;
    }
$result = pg_execute($conn, "my_query", array());

$rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }
    
if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
    echo "[{\"NOTE\":'No data'}]";
}

require_once("./json_paginazione.php");



exit(0);
}


?>