<?php
require_once '../session.php';
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$id_piazzola = $_POST['id_piazzola'];
$tipo_elemento = $_POST['tipo_elemento'];
$priorita = $_POST['priorita'];
$tipo_intervento = $_POST['tipo_int'];
$tipo_intervento_descr = $_POST['tipo_int_desc'];
$note = $_POST['note_man'];
$id_elemento = $_POST['id_elemento'];

/*echo "id_piazzola: ".$id_piazzola."<br>";
echo "tipo_elemento: ".$tipo_elemento."<br>";
echo "priorita: ".$priorita."<br>";
echo "tipo_intervento: ".$tipo_intervento."<br>";
echo "note: ".$note."<br>";
echo "id elemento: ".$id_elemento."<br>";
echo "tipo_intervento_descr: ".$tipo_intervento_descr."<br>";*/


#exit();


# aggiungo l'interevento per l'elemento
$add_intervento = "INSERT INTO gestione_oggetti.intervento
(descrizione, tipo_priorita_id, data_creazione, elemento_id, piazzola_id, utente)
VALUES($1, $2, NOW(), $3, $4, $5) returning id";

$result_add_intervento = pg_prepare($conn_sit, "add_intervento", $add_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

$result_add_intervento = pg_execute($conn_sit, "add_intervento", array($tipo_intervento_descr, $priorita, $id_elemento, $id_piazzola, $_SESSION['username']));
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

# recupero l'id_interevento dalla query precedente
$new_id_intervento=0;
$arr_int = pg_fetch_array($result_add_intervento, 0, PGSQL_NUM);
$new_id_intervento=intval($arr_int[0]);

// insert su getsione-oggetti.intervento_tipo_intervento
$insert_tipo_intervento = "INSERT INTO gestione_oggetti.intervento_tipo_intervento
(intervento_id, data_creazione, tipo_intervento_id)
VALUES($1, now(), $2);";

$result_add_tipo_intervento = pg_prepare($conn_sit, "add_tipo_intervento", $insert_tipo_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

$result_add_tipo_intervento = pg_execute($conn_sit, "add_tipo_intervento", array($new_id_intervento, $tipo_intervento));
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

// insert su gestione_oggetti.intervento_tipo_stato_intervento
$insert_stato_intervento = "INSERT INTO gestione_oggetti.intervento_tipo_stato_intervento
(tipo_stato_intervento_id, intervento_id, data_ora)
VALUES(1, $1, now());";

$result_add_stato_intervento = pg_prepare($conn_sit, "add_stato_intervento", $insert_stato_intervento);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

$result_add_stato_intervento = pg_execute($conn_sit, "add_stato_intervento", array($new_id_intervento));
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

// preparo il log sulla history
$desc_hist= $tipo_intervento_descr.' tipo_elemento = '. $tipo_elemento.' da piazzola '.$id_piazzola. '(id elemento ='.$id_elemento.')';
$sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
description, datetime, 
id_user,
id_piazzola, id_elemento, id_utenzapap) 
VALUES('PIAZZOLA', 'UPDATE_ELEM', $1, now(),
(select id_user from util.sys_users su where \"name\" ilike $2), 
$3, $4, null);";

$result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
if (pg_last_error($conn_sit)){
    echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

if ($res_ok==0) {
    $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                        $_SESSION['username'],
                        $id_piazzola, 
                        $id_elemento));
    if (pg_last_error($conn_sit)){
        echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }

    pg_query($conn_sit, "COMMIT");
    echo 'Operazione terminata correttamente';

} else {
    pg_query($conn_sit, "ROLLBACK");
    echo '<br>ERRORE<br>';
    http_response_code(400);
}

?>