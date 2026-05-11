<?php
session_start();

//require_once('../req.php');
require_once('../conn.php');


$res_ok=0;


//if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//if ($_POST['submit']) {
     //Save File        


    
    $data = json_decode(file_get_contents("php://input"), true);
    $send_id = $data['area_id'];
    $send_nome = $data['area_nome'];
    //echo "ID da eliminare: " . $delete_id."<br>";
    //echo "username: " .$_SESSION['username']."<br>";

    #exit();


    $query_eco= 'UPDATE etl.aree_4326 SET ecopunto=true WHERE id=$1 and "user" = $2 and ecopunto is not true;';
        $resulteco =pg_prepare($conn, "my_query_eco", $query_eco);
        if (pg_last_error($conn)){
            echo pg_last_error($conn);
            $res_ok=$res_ok+1;
        }
        $resulteco = pg_execute($conn, "my_query_eco", array($send_id, $_SESSION['username']));
        if (pg_last_error($conn)){
            echo pg_last_error($conn);
            $res_ok=$res_ok+1;
        }


      header('Content-Type: application/json');

    $rows = pg_affected_rows($resulteco);

    if ($rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "L'area è stata correttamente inviata a Saltax"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Nessuna area inviata, probabilmente l'area '" . $send_nome . "' non è stata inserita con il tuo utente o è già stata inviata in precedenza"
        ]);
    }
    exit;
?>
