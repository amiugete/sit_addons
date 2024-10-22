<?php 

session_start();
#require_once('./req.php');



if ($_SESSION['test']==1) {
    require_once ('./conn_test.php');
} else {
    require_once ('./conn.php');
}


//echo $_SESSION['username'] ."<br>";
//exit;

$id_piazzola=$_POST['id_piazzola'];

//echo $id_piazzola."<br>";


$civ=$_POST['civ'];

if (!$civ){
    $civ=NULL;
}
//echo $civ."<br>";


$cciv=$_POST['cciv'];

if (!$cciv){
    $cciv=NULL;
}

$lciv=$_POST['lciv'];

if (!$lciv){
    $lciv=NULL;
}


$rif=$_POST['rif'];
//echo $rif."<br>";


$note=$_POST['note'];
if (!$note){
    $note=NULL;
}

//echo $note."<br>";

/*if ($_POST['privato'] == 'privato'){
    $privato=1;
} else {
    $privato=0;
}*/
$privato=$_POST['privato'];

//echo $privato."<br>";




$query_1="UPDATE elem.piazzole
SET riferimento=$1, numero_civico=$2, 
note=$3, suolo_privato =$4, modificata_da=$5,
lettera_civico=$6, colore_civico=$7
WHERE id_piazzola = $8";


$result4 = pg_prepare($conn_sovr, "my_query_update", $query_1);
echo  pg_last_error($conn_sovr);
//$result4 = pg_execute($conn_sovr, "my_query4", array($rif, $testo_civ, $id_asta, $note, $privato, $id_transitabilita, $new_id, $lon, $lat));
$result4 = pg_execute($conn_sovr, "my_query_update", array($rif, $civ, $note, $privato, $_SESSION['username'], $lciv, $cciv, $id_piazzola));
echo  pg_last_error($conn_sovr);
$status5= pg_result_status($result4);
//echo "Status1=".$status1."<br>";



//header('Location: piazzola.php?piazzola='.$id_piazzola.'');

?>