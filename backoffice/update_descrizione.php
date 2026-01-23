<?php
session_start();
#require('../validate_input.php');





if ($_SESSION['test']==1) {
    //echo "CONNESSIONE TEST<br>";
    $checkTest=1;
    require_once ('../conn_test.php');
} else {
    //echo "CONNESSIONE ESERCIZIO<br>";
    $checkTest=0;
    require_once ('../conn.php');
}

$res_ok=0;



$desc = $_POST['desc'];
//echo $desc."<br>";

$cod_percorso = $_POST['id_percorso'];
//echo $cod_percorso."<br>";



$vers = intval($_POST['old_vers']);
//echo $vers."<br>";








//exit();



if ($checkTest==0){
    
    # mofifico anche su UNIOPE

    // update data_disattivazione = domani di quanto attivo fino ad ora

    $update_uo= "UPDATE ANAGR_SER_PER_UO aspu
    SET DESCRIZIONE = :c0
    WHERE ID_PERCORSO = :c1 
    AND DTA_DISATTIVAZIONE > SYSDATE";

    $result_uo0 = oci_parse($oraconn, $update_uo);
    # passo i parametri
    oci_bind_by_name($result_uo0, ':c0', $desc);
    oci_bind_by_name($result_uo0, ':c1', $cod_percorso);
    oci_execute($result_uo0);
    oci_free_statement($result_uo0);

}



$update_sit0="UPDATE elem.percorsi p
SET descrizione = $1
where cod_percorso LIKE $2 and (data_dismissione is null or data_dismissione> now())";

$result_usit0 = pg_prepare($conn_sit, "update_sit0", $update_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn_sit, "update_sit0", array($desc, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

//echo "<br><br>Update elem.percorsi<br>";

$descrizione_storico='Nuova descrizione percorso: '.$desc;
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

$result_isit0 = pg_prepare($conn_sit, "insert_sit0", $insert_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_isit0 = pg_execute($conn_sit, "insert_sit0", array($descrizione_storico,  $_SESSION['username'], $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn_sit);
    echo $descrizione_storico;
    echo $cod_percorso;
    echo $_SESSION['username'];
    echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}




$update_sit1="UPDATE anagrafe_percorsi.elenco_percorsi ep
SET descrizione = $1, data_ultima_modifica=now() 
where cod_percorso LIKE $2 and data_fine_validita > now()";

$result_usit1 = pg_prepare($conn_sit, "update_sit1", $update_sit1);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_usit1 = pg_execute($conn_sit, "update_sit1", array($desc, $cod_percorso)); 

if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}







$update_sit2="UPDATE anagrafe_percorsi.elenco_percorsi_old epo
SET descrizione = $1
where cod_percorso LIKE $2 and data_fine_validita > now()";


$result_usit2 = pg_prepare($conn_sit, "update_sit2", $update_sit2);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



$result_usit2 = pg_execute($conn_sit, "update_sit2", array($desc, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


// Inserisco in una cartella di log per poi far girare script di sincronizzazione python 

$insert_etl_log="INSERT INTO etl.update_descrizioni (cod_percorso, versione, new_desc) VALUES
($1, $2, $3);";

$result_usit2 = pg_prepare($conn_sit, "insert_etl_log", $insert_etl_log);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



$result_usit2 = pg_execute($conn_sit, "insert_etl_log", array($cod_percorso, $vers, $desc)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo "<br><br>Update anagrafe_percorsi.elenco_percorsi_old<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}


/*$update_sit3="UPDATE anagrafe_percorsi.percorsi_ut epo
SET descrizione = $1
where cod_percorso LIKE $2 and data_disattivazione > now()";

$result_usit3 = pg_prepare($conn_sit, "update_sit3", $update_sit3);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_usit3 = pg_execute($conn_sit, "update_sit3", array($desc, $cod_percorso)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

echo "<br><br>Update anagrafe_percorsi.percorsi_ut<br>";*/


//exit;


//header("location: ../dettagli_percorso.php?cp=".$cod_percorso."&v=".$vers."");

if ($res_ok==0){
    echo '<font color="green"> Nuova descrizione salvata correttamente!</font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>