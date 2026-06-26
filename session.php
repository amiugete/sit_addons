<?php 

if (session_status() === PHP_SESSION_ACTIVE) {
    die("SESSIONE GIA' PARTITA PRIMA DI session.php");
}

require_once 'env.php';
if ($_ENV['APP_ENV'] ==='test'){
    $path_web = '/sit_addons_test';
} else {
    $path_web = '/sit_addons';
}

if (session_status() === PHP_SESSION_NONE) {

    
    session_name('sit_addons_'.$_ENV['APP_ENV']);

    

    session_set_cookie_params([
        'path' => $path_web,
        'httponly' => true,
        /*'secure' => true,     // se usi HTTPS */
        'samesite' => 'Lax' // Strict potrebbe essere troppo restrittivo
    ]);

    session_start();
    //echo 'Sessione inizializzata';
} else {
    echo session_status();   //  2 vuol dire sessione già attiva 
    echo '<br>';
    echo session_name();     // cosa stampa? PHPSESSID oppure il tuo nome?
    //die("C'è qualche casino nella sessione ");
}
?>