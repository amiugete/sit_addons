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
    
require_once ('./query_consuntivazione_ekovision.php');

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



// ultimo mese
if($_GET['data_inizio']) {
    $query_temp= $query0 ." WHERE vrpe.DATA_PIANIFICATA between to_date(:d1 ,'YYYY-MM-DD') AND to_date(:d2 ,'YYYY-MM-DD')". $query1;
} else {
    $query_temp = $query0 ." WHERE vrpe.DATA_PIANIFICATA >= (to_date(CURRENT_DATE) - INTERVAL '1' month) ". $query1;
}


if($_GET['ut']>0) {
    #echo "sono nell'if";
    $query= "select * from (".$query_temp.") a where ',' || ID_UTS || ',' LIKE '%,'||:u1||',%'".$filter ;  
} else if (is_null($_SESSION["id_uos"])){
    $query= "select * from (".$query_temp.") a  WHERE 1=1 ".$filter;
    #echo "sono nell'eslse if";
} else {
    #echo "sono nell'else";
    //require_once("../query_ut.php");
    $uos=explode(",", $_SESSION["id_uos"]);
    $query= "select * from (".$query_temp.") a  where (ID_UTS = '".$uos[0]."' OR ID_UTS LIKE '".$uos[0].",%' OR ID_UTS LIKE '%, ".$uos[0]."' OR ID_UTS LIKE '%, ".$uos[0].",%'" ;
    if (count($uos)>1) {
        $uu=1;
        //if ($uu <= count($uos)) {
            //echo "sono nell'if <br>";
            //$query= $query . " OR ID_UTS = '".trim($uos[$uu])."' OR ID_UTS LIKE '".trim($uos[$uu]).",%' OR ID_UTS LIKE '%, ".trim($uos[$uu])."' OR ID_UTS LIKE '%, ".trim($uos[$uu]).",%'" ;
            //$uu=$uu+1;
        //}
        while($uu < count($uos)) {
            $query= $query . " OR ID_UTS = '".trim($uos[$uu])."' OR ID_UTS LIKE '".trim($uos[$uu]).",%' OR ID_UTS LIKE '%, ".trim($uos[$uu])."' OR ID_UTS LIKE '%, ".trim($uos[$uu]).",%'" ;
            #echo "uos[uu]=".$uos[$uu]."<br>";
            $uu=$uu+1;
        }
    }
    $query= $query.') '.$filter;
}

#echo $query;
#echo "<br><br>";
#echo gettype($_GET['ut']);
#echo "<br><br>".$_SESSION["id_uos"];
#echo "<br><br>".count($uos);
#exit();



$result = oci_parse($oraconn, $query);


if($_GET['ut']) {
    oci_bind_by_name($result, ':u1', $_GET['ut']);
}
if($_GET['data_inizio']) {
    oci_bind_by_name($result, ':d1', $_GET['data_inizio']);
    oci_bind_by_name($result, ':d2', $_GET['data_fine']);
}

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

require_once("./json_paginazione.php");

exit();
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

exit(0);
}


?>