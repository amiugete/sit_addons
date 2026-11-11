<?php
require_once '../session.php';
#require('../validate_input.php');


###############################################################
# RICORDARSI DI GESTIRE IL RIPRISTINO LATO EKOVISION 
# PRIMA DELLA MESSA IN PRODUZIONE
##############################################################


require_once '../conn_ok.php';

$res_ok = 0;

$id_piazzola = intval(explode('_',$_POST['id_piazzola'])[0]);

$tipo_elemento = intval(explode('_',$_POST['id_piazzola'])[1]);

if (isset($_POST['ids_elementi']) && !empty($_POST['ids_elementi'])) {
    $check_piazzola = 0;
    $messaggio_success = 'Elemento/i ripristinati correttamente.';
    $ids_elementi = explode(',', $_POST['ids_elementi']);
    $id_utenzapap = intval($_POST['id_utenzapap']);
} else {
    $check_piazzola = 1;
    $messaggio_success = 'Piazzola ripristinata correttamente.';
    
    $select_all_ids = "SELECT string_agg(id_elemento::text, ',') AS ids_elementi from elem.elementi 
    where id_piazzola = $1 and data_disattivazione_temp is not null";
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
    echo 'dismetto la piazzola';
}else{
    echo 'dismetto elemento/i';
}

//echo $ids_elementi;
foreach($ids_elementi as $id){
    echo $id.'<br>';
}


exit();*/

pg_query($conn_sit, "BEGIN");

// formato array postgres: {1,2,3}
$pg_array_ids = '{' . implode(',', $ids_elementi) . '}';

$riprist_elementi = 'UPDATE elem.elementi
SET data_disattivazione_temp=null
WHERE id_elemento = ANY($1::int[])';

$result_riprist = pg_prepare($conn_sit, "riprist_elementi", $riprist_elementi);
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

$result_riprist = pg_execute($conn_sit, "riprist_elementi", array($pg_array_ids));
if (pg_last_error($conn_sit)) {
    echo pg_last_error($conn_sit);
    $res_ok = $res_ok + 1;
}

/* VERIFICARE SE NON TUTTI GLI ELEMENTI DELLA PIAZZOLA SONO SOSPESI, ALLORA RIPRISTINO LA PIAZZOLA */
$check_piazzola = 'SELECT bool_and(data_disattivazione_temp IS NOT NULL) AS all_dismiss
FROM elem.elementi
WHERE id_piazzola = $1;';

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
if ($row['all_dismiss'] === 'f' || $check_piazzola == 1) {
    $ripristina_piazzola = 'UPDATE elem.piazzole
    SET sospesa=null
    WHERE id_piazzola = $1';

    $result_riprist_p = pg_prepare($conn_sit, "ripristina_piazzola", $ripristina_piazzola);
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $result_riprist_p = pg_execute($conn_sit, "ripristina_piazzola", array($id_piazzola));
    if (pg_last_error($conn_sit)) {
        echo pg_last_error($conn_sit);
        $res_ok = $res_ok + 1;
    }

    $desc_hist_piazzola= 'Ripristinata piazzola';
    $sql_history_piazzola= "INSERT INTO util.sys_history (\"type\", \"action\", 
            description, datetime, 
            id_user,
            id_piazzola, id_percorso, id_elemento, id_utenzapap) 
            VALUES('PIAZZOLA', 'UPDATE', $1, now(),
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

/* preparo recupero id del percorso*/
    // CICLO SUI PERCORSI 
   $select_percorsi= 'SELECT distinct id_percorso from elem.aste_percorso ap 
        where id_asta_percorso in (
            select distinct id_asta_percorso
            from elem.elementi_aste_percorso eap where id_elemento in  
            (select id_elemento from elem.elementi e 
            where e.id_piazzola = $1 and e.tipo_elemento = $2)
        ) and id_percorso in 
        (
        select id_percorso from elem.percorsi where id_categoria_uso in (3,6)
        )';
    $result_p = pg_prepare($conn_sit, "select_percorsi", $select_percorsi);
    //echo  pg_last_error($conn_sit);
    if (pg_last_error($conn_sit)){
        //echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
    
    $result_p = pg_execute($conn_sit, "select_percorsi", array($id_piazzola, $tipo_elemento));
    if (pg_last_error($conn_sit)){
        //echo pg_last_error($conn_sit);
        $res_ok=$res_ok+1;
    }
    $id_percorso = array();

    while ($ridp = pg_fetch_assoc($result_p)) {
            $id_percorso[] = $ridp['id_percorso'];
    }

if ($res_ok==0) {

    foreach($ids_elementi as $id_elemento){

        $desc_hist= 'Ripristinato elem tipo '. $tipo_elemento;
        $sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
                description, datetime, 
                id_user,
                id_piazzola, id_percorso, id_elemento, id_utenzapap) 
                VALUES('PERCORSO', 'UPDATE_ELEM', $1, now(),
                (select id_user from util.sys_users su where \"name\" ilike $2), 
                $3, $4, $5, null);";
    
        $result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
        if (pg_last_error($conn_sit)){
            $res_ok=$res_ok+1;
        }

        // ciclo sui percorsi per scrivere nella history
        foreach($id_percorso as $idp) {
    
            $result_h = pg_execute($conn_sit, "sql_history", array($desc_hist, 
                                                    $_SESSION['username'],
                                                    $id_piazzola, 
                                                    $idp,
                                                    $id_elemento));
            if (pg_last_error($conn_sit)){
                //echo pg_last_error($conn_sit);
                $res_ok=$res_ok+1;
            }
    
        }
    
    }
    
    pg_query($conn_sit, "COMMIT");
    echo $messaggio_success;
} else {
    pg_query($conn_sit, "ROLLBACK");
    echo '<br>ERRORE<br>';
    http_response_code(400);
}

?>