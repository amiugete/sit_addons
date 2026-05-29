<?php
// CARICA LE VARIABILI DALL'ENV
require_once __DIR__ . '/vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

try {
    $dotenv->load();
    //echo "ENV LOADED";
} catch (Throwable $e) {
    die("ENV ERROR: " . $e->getMessage());
}


$env = $_ENV['APP_ENV'] ?? null;

/*
if ($env === 'test') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}
*/
//echo"Non arrivo qua";

/************************************************** */
?>