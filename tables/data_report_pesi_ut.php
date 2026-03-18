<?php
session_start();
#require('../validate_input.php');




if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}

if ($_SESSION['username']){
    $user=$_SESSION['username'];
} else {
    $user= $_COOKIE['un'];
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    
require_once ('./query_report_pesi_ut.php');


$filter='';

if ($_GET['filter']){
    foreach(json_decode($_GET['filter']) as $key => $val) {
        $filter = $filter." AND upper(".$key.") LIKE upper('%".$val."%') ";
         
    }
} 

//echo 'date_inizio: '.$_GET['data_inizio'].'<br>';
//echo 'date_fine: '.$_GET['data_fine'].'<br>';
//echo 'ut: '.$_GET['ut'].'<br>';
//exit();


if($_GET['data_inizio']) {
    $query_temp= $query0 ." WHERE data_percorso between to_date($2, 'YYYY-MM-DD') and to_date($3, 'YYYY-MM-DD')".$query00;
} else {
    $query_temp = $query0 ." WHERE 0=0 ".$query00;
}


 if($_GET['ut']>0) {
        $query= "select * from (".$query_temp.") a where coalesce(id_ut, id_rimessa) = $1 ".$filter ;  
} else {
    require_once("../query_ut.php");
    $query= "select * from (".$query_temp.") a 
            where COALESCE(id_ut, id_rimessa) IN (select x.id_uo from (".$query_ut.") x )".$filter;
}

//echo $query;
//echo "<br><br>";
//echo $_GET['ut'];
//echo "<br><br>".$_SESSION["id_uos"];
//echo "<br><br>".count($_SESSION["id_uos"]);
//exit();

$result = pg_prepare($conn, "my_query", $query);
    if (pg_last_error($conn)){
        echo pg_last_error($conn);
        exit;
    }

if($_GET['ut']) {
    $result = pg_execute($conn, "my_query", array($_GET['ut'], $_GET['data_inizio'], $_GET['data_fine']));  
} else {
    $result = pg_execute($conn, "my_query", array($user, $_GET['data_inizio'], $_GET['data_fine']));}



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


}


?>