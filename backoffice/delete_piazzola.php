<?php

session_start();
#require('../validate_input.php');


if ($_SESSION['test']==1) {
    require_once ('../conn_test.php');
} else {
    require_once ('../conn.php');
}


$res_ok=0;




$id_piazzola = $_POST['id_piazzola'];



// cerco se ci sono elementi nei percorsi e li elimino

$descr_hist='Eliminati tutti gli elementi da piazzola '. $id_piazzola.'';

$query_hist = "
SELECT elem.elimina_piazzola(
    $1; $2, $3                                   
);";


// da usare $conn_sit

// $1 --> descr_hist
// $2 --> $_SESSION['username]
// $3 --> $id_piazzola


// da capire come intercettare il return e gli output testuali


?> 