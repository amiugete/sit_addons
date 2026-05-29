<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//require_once('../req.php');
require_once('../conn_ok.php');


$res_ok=0;


//if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//if ($_POST['submit']) {
     //Save File        


    
    $data = json_decode(file_get_contents("php://input"), true);
    $delete_id = $data['area_id'];
    $delete_nome = $data['area_nome'];
    //echo "ID da eliminare: " . $delete_id."<br>";
    //echo "username: " .$_SESSION['username']."<br>";

    #exit();



    $query_delete = 'DELETE FROM etl.aree_4326 WHERE id = $1 and "user" = $2 and ecopunto is not true;';

    $result_del =pg_prepare($conn, "query_delete", $query_delete);
    if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
    }

    $result_del = pg_execute($conn, "query_delete", array($delete_id, $_SESSION['username']));
    if (pg_last_error($conn)){
        echo pg_last_error($conn);
        $res_ok=$res_ok+1;
    }

    header('Content-Type: application/json');

    $rows = pg_affected_rows($result_del);

    if ($rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "L'area è stata correttamente eliminata"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Nessuna area eliminata, probabilmente l'area '" . $delete_nome . "' non è stata inserita con il tuo utente o si tratta di un ecopunto"
        ]);
    }
    exit;
?>
