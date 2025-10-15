<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;



$targa = str_replace(" ","",$_POST['targa']) ;
//echo $desc."<br>";


$ut = intval($_POST['ut0']);
//echo $vers."<br>";








//exit();




$insert_sit0="INSERT INTO etl.mezzi_ditte_terze 
(id_uo, targa, in_uso, data_inserimento) 
VALUES($1, upper($2), true, now());";

$result_usit0 = pg_prepare($conn, "insert_sit0", $insert_sit0);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update quintali<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn, "insert_sit0", array($ut, $targa)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update quintali<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}

if ($_POST['quintali']){
    $update_sit1="UPDATE etl.mezzi_ditte_terze 
    SET quintali=$1 
    WHERE id_uo=$2 AND targa=$3 ;";
   
   $result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
    if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo "<br><br>Update quintali<br>". pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
    $result_usit1 = pg_execute($conn, "update_sit1", array($_POST['quintali'], $ut, $targa)); 
    
    if (!pg_last_error($conn)){
        #$res_ok=0;
    } else {
        echo "<br><br>Update quintali<br>". pg_last_error($conn);
        $res_ok= $res_ok+1;
    }
}











if ($res_ok==0){
    echo '<font color="green"> Nuova targa inserita correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>