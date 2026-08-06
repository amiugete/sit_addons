<?php
require_once '../session.php';
#require('../validate_input.php');




require_once '../conn_ok.php';


$dataInizio = $_GET['data_inizio'] ?? null;
$dataFine   = $_GET['data_fine'] ?? null;
$ut         = $_GET['ut'] ?? null;

if ($_SESSION['username']){
    $user=$_SESSION['username'];
} else {
    $user= $_COOKIE['un'];
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    
require_once ('./query_report_pesi_percorso.php');



# da rivedere il filtro per la ricerca
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


if($dataInizio) {
    $query_temp= $query0 ." WHERE data_percorso between to_date($2, 'YYYY-MM-DD') and to_date($3, 'YYYY-MM-DD')";
} else {
    $query_temp = $query0 ." WHERE 0=0 ";
}


 if($ut) {
        $query= "select * from (".$query_temp.") a where $1 IN (id_ut_titolare, id_ut_esecutrice) ".$filter ;  
} else {
    require_once("../query_ut.php");
    // in questo caso devo confrontare 2 array con l'operatore && (array overlap) per vedere se l'utente loggato ha accesso a uno dei due ut_titolare o ut_esecutrice
    $query= "select * from (".$query_temp.") a 
            where ARRAY[id_ut_titolare, id_ut_esecutrice] && ARRAY(select x.id_uo from (".$query_ut.") x )".$filter;
}


$query = $query . " order by data_percorso desc, dataoraconf desc";
//echo $query. '<br><br>';
//echo $user. '<br><br>'. $dataInizio. '<br><br>'.$dataFine. '<br><br>';
//exit();
//echo "<br><br>";
//echo $_GET['ut'];
//echo "<br><br>".$_SESSION["id_uos"];
//echo "<br><br>".count($_SESSION["id_uos"]);
//exit();

$result = pg_prepare($conn, "my_query", $query);

if ($result === false) {
    die(pg_last_error($conn));
}


if($ut) {
    $result = pg_execute($conn, "my_query", array($ut, $dataInizio, $dataFine));  
} else {
    $result = pg_execute($conn, "my_query", array($user, $dataInizio, $dataFine));
}


if ($result === false) {
    die(pg_last_error($conn));
}


$rows = array();
    while($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
        //print $r['id'];
    }
    
if (empty($rows)==FALSE){
    //print $rows;
    $json = json_encode(array_values($rows));
} else {
    echo json_encode([
        ['NOTE' => 'No data']
    ]);

}

require_once("./json_paginazione.php");


}


?>