<?php
require_once '../session.php';
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$id_piazzola = $_POST['id_piazzola'];
$riferimento= isset($_POST['riferimento']) && $_POST['riferimento'] !== '' ? $_POST['riferimento'] : null;
$civico = isset($_POST['civico']) && $_POST['civico'] !== '' ? $_POST['civico'] : null;
$ecopunto = isset($_POST['ecopunto']) ? 1 : 0;
$note = isset($_POST['note']) && $_POST['note'] !== '' ? $_POST['note'] : null;
$transitabilita = isset($_POST['transitabilita']) && $_POST['transitabilita'] !== '' ? $_POST['transitabilita'] : null;
$criticita = $_POST['criticita'] ?? [];
$asta_servizio = isset($_POST['id_asta_servizio']) && $_POST['id_asta_servizio'] !== '' ? $_POST['id_asta_servizio'] : null;
$suolo_privato = isset($_POST['suolo_privato']) ? 1 : 0;

/*echo "id_piazzola: ".$id_piazzola."<br>";
echo "riferimento: ".$riferimento."<br>";
echo "civico: ".$civico."<br>";
echo "ecopunto: ".$ecopunto."<br>";
echo "note: ".$note."<br>";
echo "transitabilita: ".$transitabilita."<br>";
echo "criticita: ".implode(", ", $criticita)."<br>";
echo "asta_servizio: ".$asta_servizio."<br>";
echo "suolo_privato: ".$suolo_privato."<br>";

foreach ($criticita as $key => $value) {
    echo "criticita[$key]: ".$value."<br>";
}*/


$civ_list = str_split($civico);
if (count($civ_list) > 1){
    $numero_civico = $civ_list[0];
    $lettera_civico = $civ_list[1];
    if (count($civ_list) > 2){
        $colore_civico = $civ_list[2];
    } else {
        $colore_civico = NULL;
    }
}else {
    $numero_civico = $civ_list[0];
    $lettera_civico = NULL;
    $colore_civico = NULL;
}

/*echo "numero_civico: ".$numero_civico."<br>";
echo "lettera_civico: ".$lettera_civico."<br>";
echo "colore_civico: ".$colore_civico."<br>";*/

$select_criticita = "SELECT id_criticita from elem.criticita_piazzole where id_piazzola = $1";
$result_criticita = pg_prepare($conn_sit, "select_criticita", $select_criticita);
$result_criticita = pg_execute($conn_sit, "select_criticita", array($id_piazzola));
$criticita_db = [];

while($row = pg_fetch_assoc($result_criticita)) {

    $criticita_db[] = $row['id_criticita'];
}

// verifico se il form invia criticità non presenti su db 
$criticita_da_inserire = array_diff($criticita, $criticita_db);

// verifico se il form NON invia criticità presenti su DB
$criticita_da_eliminare = array_diff($criticita_db, $criticita);

/*echo 'criticità form: '.implode(", ", $criticita)."<br>";
echo 'criticità db: '.implode(", ", $criticita_db)."<br>";
echo 'criticità da aggiungere: '.implode(", ", $criticita_da_inserire)."<br>";
echo 'criticità da eliminare: '.implode(", ", $criticita_da_eliminare)."<br>";

exit();*/

try {
    /* FACIO TUTTO IN UNA TRANSACTION*/
    pg_query($conn_sit, "BEGIN");

    //se su db ci sono criticità non inviate dal form le elimino
    if (count($criticita_da_eliminare) > 0) {
        $del_criticita = "DELETE FROM elem.criticita_piazzole WHERE id_piazzola=$1 AND id_criticita=$2;";
        foreach ($criticita_da_eliminare as $key => $value) {
            $result_del_criticita = pg_prepare($conn_sit, "del_criticita_$key", $del_criticita);
            $result_del_criticita = pg_execute($conn_sit, "del_criticita_$key", array($id_piazzola, $value));
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            }
        }
    }

    // se il form invia delle criticità non presenti su db le inserisco
    if (count($criticita_da_inserire) > 0) {
        $insert_criticita = "INSERT INTO elem.criticita_piazzole
        (id_criticita, id_piazzola, data_insert)
        VALUES($1, $2, now());";
        foreach ($criticita_da_inserire as $key => $value) {
            $result_insert_criticita = pg_prepare($conn_sit, "insert_criticita_$key", $insert_criticita);
            $result_insert_criticita = pg_execute($conn_sit, "insert_criticita_$key", array($value, $id_piazzola));
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            }
        }
    }

    // aggiorno gli altri dettagli della piazzola
    $query_update_piazzola="UPDATE elem.piazzole
    SET riferimento=$1, numero_civico=$2, 
    note=$3, suolo_privato =$4, modificata_da=$5,
    lettera_civico=$6, colore_civico=$7, 
    data_ultima_modifica=now(), ecopunto=$8,
    id_transitabilita=$9, id_asta_servizio=$10
    WHERE id_piazzola = $11";

    $result_update_piazzola = pg_prepare($conn_sit, "update_piazzola", $query_update_piazzola);
        if (pg_last_error($conn_sit)){
            throw new Exception(pg_last_error($conn_sit));
        }


    $result_update_piazzola = pg_execute($conn_sit, "update_piazzola", array($riferimento, $numero_civico, 
        $note, $suolo_privato, $_SESSION['username'], 
        $lettera_civico, $colore_civico,
        $ecopunto, $transitabilita, $asta_servizio,
        $id_piazzola));
    if (pg_last_error($conn_sit)) {
        throw new Exception(pg_last_error($conn_sit));
    }


    // log dell'operazione
    $sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
                description, datetime, 
                id_user, id_piazzola, 
                id_percorso, id_elemento, 
                id_utenzapap) 
                VALUES('PIAZZOLA', 'UPDATE', 
                'modificati dettagli', now(),
                (select id_user from util.sys_users su where \"name\" ilike $1), $2, 
                null, null, null);";
    
    $result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
    if (pg_last_error($conn_sit)){
        throw new Exception(pg_last_error($conn_sit));
    }


    $result_h = pg_execute($conn_sit, "sql_history", array($_SESSION['username'], $id_piazzola));
    if (pg_last_error($conn_sit)) {
        throw new Exception(pg_last_error($conn_sit));
    }
    

    pg_query($conn_sit, "COMMIT");
    http_response_code(200);
    //echo '<font color="green">Elemento aggiunto correttamente. </font>';

    echo json_encode([
        'ok' => true,
        'messaggio' => 'DETTAGLI PIAZZOLA AGGIORNATI CORRETTAMENTE'
    ]);
}            
catch (Exception $e) {
    pg_query($conn_sit, "ROLLBACK");
    echo "IMPOSSIBILE AGGIORNARE I DETTAGLI DELLA PIAZZOLA. Errore: " . $e->getMessage();
    http_response_code(400);
} 



?>