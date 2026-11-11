<?php
require_once '../session.php';
#require('../validate_input.php');


require_once '../conn_ok.php';

/* Abbiamo spostato quasi tutto il delete degli elementi sulla funzione pgsql history.trg_elementi_delete()*/

$res_ok = 0;

$id_piazzola = intval(explode('_',$_POST['id_piazzola'])[0]);

if (isset($_POST['ids_elementi']) && !empty($_POST['ids_elementi'])) {
    $check_piazzola = 0;
    $messaggio_success = 'Elemento/i eliminato/i correttamente.';
    $ids_elementi = explode(',', $_POST['ids_elementi']);
    //$id_utenzapap = intval($_POST['id_utenzapap']);
} else {
    $check_piazzola = 1;
    $messaggio_success = 'Piazzola eliminata correttamente.';
    
    $select_all_ids = "SELECT string_agg(id_elemento::text, ',') AS ids_elementi from elem.elementi where id_piazzola = $1";
    $result_all_ids = pg_prepare($conn_sit, "select_all_ids", $select_all_ids);
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $result_all_ids = pg_execute($conn_sit, "select_all_ids", array($id_piazzola));
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $row_all_ids = pg_fetch_assoc($result_all_ids);

    $ids_elementi = explode(',',$row_all_ids['ids_elementi']);
}

/*if ($check_piazzola){
    echo 'elimino la piazzola con elementi '.$pg_array_ids;
}else{
    echo 'elimino elemento/i '.$pg_array_ids;
}

exit();*/

pg_query($conn_sit, "BEGIN");

// imposto l'utente corrente per la history, valida per la transazione
$set_user = "SELECT set_config('app.username', $1, true)";
$result_set_user = pg_prepare($conn_sit, "set_user", $set_user);
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

$result_set_user = pg_execute($conn_sit, "set_user", array($_SESSION['username']));
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

// formato array postgres: {1,2,3}
$pg_array_ids = '{' . implode(',', $ids_elementi) . '}';

$del_elementi = 'DELETE FROM elem.elementi WHERE id_elemento = ANY($1::int[])';
$result_del = pg_prepare($conn_sit, "del_elementi", $del_elementi);
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

$result_del = pg_execute($conn_sit, "del_elementi", array($pg_array_ids));
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

/* VERIFICARE SE TUTTI GLI ELEMENTI DELLA PIAZZOLA SONO ELIMINATI, ALLORA ELIMINARE LA PIAZZOLA */
$check_piazzola = 'SELECT NOT EXISTS (
    SELECT 1
    FROM elem.elementi
    WHERE id_piazzola = $1
) as all_deleted;';

$result_cp = pg_prepare($conn_sit, "check_piazzola", $check_piazzola);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        //echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
    
$result_cp = pg_execute($conn_sit, "check_piazzola", array($id_piazzola));
if (pg_last_error($conn_sit)){
    //echo pg_last_error($conn_sit);
    $res_ok=$res_ok+1;
}

$row = pg_fetch_assoc($result_cp);
if ($row['all_deleted'] === 't' || $check_piazzola == 1) {
    $del_piazzola = 'UPDATE elem.piazzole
    SET data_eliminazione=now()
    WHERE id_piazzola = $1';

    $result_del = pg_prepare($conn_sit, "del_piazzola", $del_piazzola);
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $result_del = pg_execute($conn_sit, "del_piazzola", array($id_piazzola));
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $desc_hist_piazzola= 'Eliminata piazzola';
    $sql_history_piazzola= "INSERT INTO util.sys_history (\"type\", \"action\", 
            description, datetime, 
            id_user,
            id_piazzola, id_percorso, id_elemento, id_utenzapap) 
            VALUES('PIAZZOLA', 'DELETE', $1, now(),
            (select id_user from util.sys_users su where \"name\" ilike $2), 
            $3, null, null, null);";

    $result_h_piazzola = pg_prepare($conn_sit, "result_h_piazzola", $sql_history_piazzola);
    if (pg_last_error($conn_sit)){
        $res_ok=$res_ok+1;
    }

    $result_h_piazzola = pg_execute($conn_sit, "result_h_piazzola", array($desc_hist_piazzola, 
                                                $_SESSION['username'], $id_piazzola));
    if (pg_last_error($conn_sit)){
        //echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
}


if ($res_ok==0) {
    pg_query($conn_sit, "COMMIT");
    echo $messaggio_success;
} else {
    pg_query($conn_sit, "ROLLBACK");
    echo '<br>ERRORE<br>';
    http_response_code(400);
}

?>