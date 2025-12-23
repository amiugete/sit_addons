<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;



$nota= $_POST['nota_vers'];
//echo $nota."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";



$vers = intval($_POST['old_vers']);
//echo $vers."<br>";


//echo "<br><br>Update elem.percorsi<br>";

$nota_storico='Nuova nota versione: '.$nota;
/*$insert_sit0="INSERT INTO util.sys_history (\"type\", \"action\", description, datetime,  id_percorso, id_user)
 VALUES( 'PERCORSO', 'UPDATE', $1 , CURRENT_TIMESTAMP, (select id_percorso from elem.percorsi 
 WHERE cod_percorso LIKE $2 and (data_dismissione is null or data_dismissione> now())), 
 (select id_user from util.sys_users su where \"name\" ilike $3));";
*/
$insert_sit0="INSERT INTO util.sys_history (\"type\", \"action\", description, datetime,  id_percorso, id_user)
(select 'PERCORSO', 'UPDATE', $1 , CURRENT_TIMESTAMP, 
 id_percorso, (select id_user from util.sys_users su where \"name\" ilike $2)
 from elem.percorsi 
 WHERE cod_percorso LIKE $3 and (data_dismissione is null or data_dismissione> now())) ;";

$result_isit0 = pg_prepare($conn, "insert_sit0", $insert_sit0);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_isit0 = pg_execute($conn, "insert_sit0", array($nota_storico,  $_SESSION['username'], $cod_percorso)); 
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn);
    echo $nota_storico;
    echo $cod_percorso;
    echo $_SESSION['username'];
    echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}




$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET nota_versione = $1, data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_fine_validita > now() and ep.versione_testa = $3";

$result_usit1 = pg_prepare($conn, "update_sit1", $update_sit1);
if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}
$result_usit1 = pg_execute($conn, "update_sit1", array($nota, $cod_percorso, $vers)); 

if (!pg_last_error($conn)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn);
    $res_ok= $res_ok+1;
}




if ($res_ok==0){
    echo '<font color="green"> Nuova nota versione salvata correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>