<?php
require_once '../session.php';
#require('../validate_input.php');
#scrivere su: elem.elementi_privati e util.sys_history.

require_once '../conn_ok.php';


$res_ok=0;

$id_piazzola = intval($_POST['id_piazzola']);
$lat = floatval($_POST['lat']);
$lon = floatval($_POST['lon']);
$buffer = 20;

echo "id_piazzola: $id_piazzola<br>";

exit();

pg_query($conn_sit, "BEGIN");

// recupero l'id asta per fare verifica su posizione nel buffer anche lato beckend
$query_asta = "SELECT id_asta
    FROM elem.piazzole
    WHERE id_piazzola = $1";

$result_asta = pg_prepare($conn_sit, "query_asta", $query_asta);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_asta = pg_execute($conn_sit, "query_asta", array($id_piazzola)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su select id_asta<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

while($row = pg_fetch_assoc($result_asta)) {
    $id_asta = $row['id_asta'];
}

// verifico che la posizione sia all'interno del buffer sull'asta
$query_check = "SELECT EXISTS (
    SELECT 1
    FROM geo.grafostradale g
    WHERE g.id = $1
    AND ST_Contains(
        ST_Buffer(g.geoloc, $2),
        ST_Transform(ST_SetSRID(ST_MakePoint($3, $4), 4326), 3003)
    )
) AS pontinbuffer";

$result_check = pg_prepare($conn_sit, "query_check", $query_check);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}
$result_check = pg_execute($conn_sit, "query_check", array($id_asta, $buffer, $lon, $lat)); 
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    echo $_SESSION['username'];
    echo "<br><br>ERRORE su check piazzola nel buffer<br>". pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$rowc = pg_fetch_assoc($result_check);

$pos_non_valida = '';

if ($row['pontinbuffer'] !== 't') {

    $pos_non_valida = ': la posizione non è valida';
    $res_ok= $res_ok+1;

}else{
    //se la nuova posizione è effettivamente nel buffer, aggiorno la piazzola e scrivo in sys_history
    $query_update = "UPDATE geo.piazzola
    SET geoloc = ST_Transform(ST_SetSRID(ST_MakePoint($1, $2), 4326), 3003)
    WHERE id_piazzola = $3";

    $result_update = pg_prepare($conn_sit, "query_update", $query_update);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_update = pg_execute($conn_sit, "query_update", array($lon, $lat, $id_piazzola)); 
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        echo $_SESSION['username'];
        echo "<br><br>ERRORE update posizione piazzola<br>". pg_last_error($conn_sit);
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
    $insert_history="INSERT INTO util.sys_history
    (\"type\", \"action\", description, datetime, id_user, id_piazzola)
    VALUES('PIAZZOLA', 'UPDATE', 'Modificata posizione', CURRENT_TIMESTAMP, $1, $2);";

    $result_history = pg_prepare($conn_sit, "insert_history", $insert_history);
    if (!pg_last_error($conn_sit)){
        #$res_ok=0;
    } else {
        pg_last_error($conn_sit);
        $res_ok= $res_ok+1;
    }
    $result_history = pg_execute($conn_sit, "insert_history", array($id_user, $id_piazzola)); 
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



}


if ($res_ok==0){
    pg_query($conn_sit, "COMMIT");
    echo 'Piazzola spostata correttamente';
} else {
    pg_query($conn_sit, "ROLLBACK");
    echo $res_ok.'ERRORE nello spostamento della piazzola'.$pos_non_valida;
    http_response_code(400);
}

?>
<?php



?>