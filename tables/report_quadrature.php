<?php
session_start();
#require('../validate_input.php');




if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}
//echo "OK";


if(!$oraconn) {
    die('Connessione fallita !<br />');
} else {
    
require_once ('./query_quadrature.php');

//$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA >= to_date('20240428', 'YYYYMMDD' )". $query1;

//da primo giorno del mese prima
//$query = $query0 ." WHERE vrpe.DATA_PIANIFICATA between add_months(trunc(sysdate,'mm'),-1) 
//and trunc(sysdate,'mm')". $query1;



if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        /*if (is_numeric($val)){
            $filter = $filter. " AND ".$key." = ".$val." ";
        } else {*/
            $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
        //} 
         
    }
} 


if ($_GET['solo_squadrati']=='t') {
    $query1= " GROUP BY au.id_uo_gest, au.desc_uo, per.nominativo, per.cod_MATLIBROMAT
    HAVING  COALESCE(sum(hs.durata),0) - COALESCE(sum(vo.MINUTI_LAV),0) <> 0
    ORDER BY 1, 3";
} else if ($_GET['solo_squadrati']=='f') {
	$query1= " GROUP BY au.id_uo_gest, au.desc_uo, per.nominativo, per.cod_MATLIBROMAT
	ORDER BY 1, 3";
}


// giorno selezionato
if($_GET['data']) {
    $query_temp= $query0 ." WHERE to_date(:datav, 'YYYYMMDD') BETWEEN per.dta_inizio AND per.dta_fine". $query1;
}


if($_GET['ut']>0) {
    $params = [
        ':datav'    => $_GET['data'],
        ':uos' => $_GET['ut'],
    ];
    #echo "sono nell'if";
    $query= "SELECT ROW_NUMBER() OVER (ORDER BY ID_UO_GEST, nominativo) AS ID, a.* FROM (".$query_temp.") a where ID_UO_GEST = :uos".$filter ;  
} else {
    $params = [
        ':datav'    => $_GET['data'],
    ];
    $query= "SELECT ROW_NUMBER() OVER (ORDER BY ID_UO_GEST, nominativo) AS ID, a.* FROM (".$query_temp.") a  WHERE 1=1 ".$filter;
    #echo "sono nell'eslse if";
}

/*echo $query;
echo "<br><br>";
echo $_GET['ut']."<br><br>";
echo $_GET['data']."<br><br>";
echo "<br><br>".$_SESSION["id_uos"];
#echo "<br><br>".count($uos);
#exit();
*/

$params = [
    ':datav'    => $_GET['data'],
    ':uos' => $_GET['ut'],
];

// Funzione helper per visualizzare la query "espansa"
function debugOciQuery(string $sql, array $params): string {
    foreach ($params as $key => $value) {
        $replacement = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        $sql = str_replace($key, $replacement, $sql);
    }
    return $sql;
}



$result = oci_parse($oraconn, $query);

/*if($_GET['data']) {
    oci_bind_by_name($result, ':datav', $_GET['data']);
}
if($_GET['ut']) {
    oci_bind_by_name($result, ':uos', $_GET['ut']);
}*/

foreach ($params as $key => &$value) {
    oci_bind_by_name($result, $key, $value);
}


/*echo debugOciQuery($query, $params);
exit();*/


oci_execute($result);
$rows = array();
while($r = oci_fetch_assoc($result)) { 
    //print $r;
    $rows[] = $r;
}
oci_free_statement($result);
oci_close($oraconn);
//pg_close($conn);
#echo $rows ;

//require_once("./json_paginazione.php");

//exit();
if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
    echo "[{\"NOTE\":'No data'}]";
}

if ($json){
    echo $json;
} else {
    echo json_last_error_msg();
}

exit();
}


?>