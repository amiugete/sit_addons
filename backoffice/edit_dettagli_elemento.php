<?php
require_once '../session.php';
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$id_elemento = $_POST['id_elemento'];
$id_elementi_tabella = $_POST['id_elementi_tabella'];
$tipo_elemento= $_POST['tipo_elemento'];
$tag = $_POST['tag'];
$matricola = $_POST['matricola'];
$serratura = $_POST['serratura'];
$matricola_serratura = $_POST['matricola_serratura'];
$id_materiale = $_POST['id_materiale'];
$percent_riempimento = $_POST['percent_riempimento'];
$freq_stimata = $_POST['freq_stimata'];

// echo "id_elemento: ".$id_elemento."<br>";
// echo "id_elementi_tabella: ".implode(", ", $id_elementi_tabella)."<br>";
// echo "tipo_elemento: ".$tipo_elemento."<br>";
// echo "tag: ".$tag."<br>";
// echo "matricola: ".$matricola."<br>";
// echo "serratura: ".$serratura."<br>";
// echo "matricola_serratura: ".$matricola_serratura."<br>";
// echo "id_materiale: ".$id_materiale."<br>";
// echo "percent_riempimento: ".$percent_riempimento."<br>";
// echo "freq_stimata: ".$freq_stimata."<br>";


// exit();

try {
    pg_query($conn_sit, "BEGIN");

    $update_dettagli =" UPDATE elem.elementi
    SET modificato_da=$1, data_ultima_modifica=now(), 
    percent_riempimento=$2, freq_stimata=$3, 
    matricola=$4, serratura=$5, 
    id_materiale=$6, tag=$7, 
    matricola_serratura=$8, tipo_elemento=$9
    WHERE id_elemento=$10;
    ";

    $result_update = pg_prepare($conn_sit, "update_element", $update_dettagli);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_update = pg_execute($conn_sit, "update_element", array($_SESSION['username'], $percent_riempimento, $freq_stimata, $matricola, $serratura, $id_materiale, $tag, $matricola_serratura, $tipo_elemento, $id_elemento));
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        //echo pg_last_error($conn_sit);
        echo $nota_storico;
        echo $cod_percorso;
        echo $_SESSION['username'];
        echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }


    $query_user = "select id_user from util.sys_users su where \"name\" ilike $1";
    $result_user = pg_prepare($conn_sit, "query_user", $query_user);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_user = pg_execute($conn_sit, "query_user", array($_SESSION['username'])); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        //echo pg_last_error($conn_sit);
        echo $_SESSION['username'];
        echo "<br><br>ERRORE su query id_user<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    while($ru = pg_fetch_assoc($result_user)) { 
        $id_user = $ru['id_user'];
    }
    
    //insert in util.sys_history
    $messagelog = "Dettagli elemento ".$id_elemento;

    $insert_history="INSERT INTO util.sys_history
    (\"type\", \"action\", description, datetime, id_user, id_elemento)
    VALUES('ELEMENTO', 'UPDATE', $1, CURRENT_TIMESTAMP, $2, $3);";

    $result_history = pg_prepare($conn_sit, "insert_history", $insert_history);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_history = pg_execute($conn_sit, "insert_history", array($messagelog, $id_user, $id_elemento)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        //echo pg_last_error($conn_sit);
        echo $nota_storico;
        echo $cod_percorso;
        echo $_SESSION['username'];
        echo "<br><br>Insert util.sys_history<br>". pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }

    if ($res_ok==0){
            pg_query($conn_sit, "COMMIT");
            http_response_code(200);
            echo json_encode([
                'ok' => true,
                'messaggio' => 'Modifiche salvate correttamente!'
            ]);
    }else{
        pg_query($conn_sit, "ROLLBACK");
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'messaggio' => "Impossibile salvare le modifiche. Errore: " . $e->getMessage()
        ]);
    }
}            
catch (Exception $e) {
    pg_query($conn_sit, "ROLLBACK");
    http_response_code(400);
    echo json_encode([
            'ok' => false,
            'messaggio' => "Impossibile salvare le modifiche. Errore: " . $e->getMessage()
    ]);
} 

?>