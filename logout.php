<?php 

session_start();

// definisco la variabile lifetime
$lifetime=86400;
session_set_cookie_params($lifetime);

// unset cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}   
setcookie("tokenCookie", '', time() + ($lifetime * 7));


die ('Logout effettuato con successo. Per riaccedere sulla pagina del <a href="./login.php" class="btn btn-info"> login </a>');

?>