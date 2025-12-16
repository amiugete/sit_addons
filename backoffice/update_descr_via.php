<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']){
    if ($_SESSION['test']==1) {
        require_once ('../conn_test.php');
    } else {
        require_once ('../conn.php');
    }
} else {
    echo 'Sessione scaduta. Si prega di ricaricare la pagina per proseguire';
    exit();
}



$res_ok=0;



$nome = trim($_POST['nome']);
//echo $desc."<br>";

$id_via = $_POST['id_via'];
//echo $cod_percorso."<br>";

$pref = $_POST['pref'];

// 1. trim + sostituzione di spazi multipli / tab / newline con un solo spazio
$stringa = trim(preg_replace('/\s+/', ' ', $nome));

$stringa =  mb_strtoupper(
    mb_convert_encoding($stringa, 'UTF-8', 'auto'),
    'UTF-8'
);
// 2. separa le parole
$parole = explode(' ', $stringa);

// 3. se c'è più di una parola, sposta la prima in fondo
if (count($parole) > 1) {
    $prima = array_shift($parole);
    $parole[] = $prima;
}

// 4. ricomponi e metti in maiuscolo gestendo accenti
$risultato = implode(' ', $parole);


//echo $risultato ."<br>";

$nome_sit=$stringa;
$via_ordinata=$risultato;

$nome1 = $via_ordinata . ' ('.$pref.')';
$nome2 = $nome_sit . ' ('.$pref. ')';


/*echo $nome_sit ."<br>";
echo $via_ordinata ."<br>";
echo $nome1 ."<br>";
echo $nome2 ."<br>";
*/
//exit();


if ($_SESSION['test']!=1) {// update data_disattivazione = domani di quanto attivo fino ad ora
    echo 'Update di strade <hr>';
    //exit();

    $update_uo= "UPDATE STRADE.STRADE
    SET NOME1 = :c0,
    NOME2 = :c1,
    NOME_STAMPA = :c2
    WHERE CODICE_VIA = :c3";

    $result_uo0 = oci_parse($oraconn, $update_uo);
    # passo i parametri
    oci_bind_by_name($result_uo0, ':c0', $nome1);
    oci_bind_by_name($result_uo0, ':c1', $nome2);
    oci_bind_by_name($result_uo0, ':c2', $nome_sit);
    oci_bind_by_name($result_uo0, ':c3', $id_via);

    oci_execute($result_uo0);
    oci_free_statement($result_uo0);

} else {
    echo 'Sono in test, non faccio update di strade <hr>';
}


$update_sit0="UPDATE topo.vie
SET nome = $1,
via_ordinata = $2,
data_ultima_modifica = now()
where id_via = $3";

$result_usit0 = pg_prepare($conn_sit, "update_sit0", $update_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_si);
    $res_ok= $res_ok+1;
}
$result_usit0 = pg_execute($conn_sit, "update_sit0", array($nome_sit, $via_ordinata, $id_via)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

//echo "<br><br>Update elem.percorsi<br>";

$descrizione_storico='Modifica nome via con id '.$id_via. ' con '.$nome_sit;
/*$insert_sit0="INSERT INTO util.sys_history (\"type\", \"action\", description, datetime,  id_percorso, id_user)
 VALUES( 'PERCORSO', 'UPDATE', $1 , CURRENT_TIMESTAMP, (select id_percorso from elem.percorsi 
 WHERE cod_percorso LIKE $2 and (data_dismissione is null or data_dismissione> now())), 
 (select id_user from util.sys_users su where \"name\" ilike $3));";
*/
$insert_sit0="INSERT INTO util.sys_history (\"type\", \"action\", description, 
datetime, id_user)
VALUES
('VIE', 'UPDATE', $1 , 
CURRENT_TIMESTAMP, 
(select id_user from util.sys_users su where \"name\" ilike $2)
) ;";

$result_isit0 = pg_prepare($conn_sit, "insert_sit0", $insert_sit0);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_isit0 = pg_execute($conn_sit, "insert_sit0", array($descrizione_storico,  $_SESSION['username'])); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    //echo pg_last_error($conn);
    echo $_SESSION['username'];
    echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit).'<br>';
    $res_ok= $res_ok+1;
}



if ($res_ok==0){
    echo '<font color="green"> Nuova descrizione salvata correttamente!
    <br>Ricarica la pagina (F5) per visualizzare i risultati corretti.
    </font>';
} else {
    echo $res_ok.'<font color="red"> ERRORE - contatta assterritorio@amiu.genova.it</font>';
}

?>