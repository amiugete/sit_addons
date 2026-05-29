<?php 

require_once 'env.php';



// Punta sempre al DB in produzione 
$dbhost=$_ENV['DB_SIT_HOST'] ?? null;
$dbport=$_ENV['DB_SIT_PORT'] ?? null;
$dbname=$_ENV['DB_SITPROD_NAME'] ?? null;
$dbuser=$_ENV['DB_SIT_USER'] ?? null;
$dbpassword=$_ENV['DB_SIT_PASS'] ?? null;

$conn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword");
if (!$conn) {
        die("<br>Could not connect to DB PostgreSQL $dbname, please contact the administrator.");
}



// da usare per pagine sul sovrariempimento (sarebbe poi forse da togliere perchè è uguale a conn_sit)
$dbname=$_ENV['DB_SIT_NAME'] ?? null;

$conn_sovr = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword");
if (!$conn_sovr) {
        die("<br>Could not connect to DB PostgreSQL $dbname, please contact the administrator.");
} 


// da usare per per SIT 2.0
$conn_sit = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword");
if (!$conn_sit) {
        die("<br>Could not connect to DB PostgreSQL $dbname, please contact the administrator.");
}

// consuntivazione totem

/*$dbhost=$_ENV['DB_T_HOST'] ?? null;
$dbport=$_ENV['DB_T_PORT'] ?? null;
$dbname=$_ENV['DB_T_NAME'] ?? null;
$dbuser=$_ENV['DB_T_USER'] ?? null;
$dbpassword=$_ENV['DB_T_PASS'] ?? null;


$conn_hub = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword");
if (!$conn_hub) {
   die("<br>Could not connect to DB PostgreSQL $dbname, please contact the administrator.");
}*/


$dbhost=$_ENV['DB_TOTEM_HOST'] ?? null;
$dbport=$_ENV['DB_T_PORT'] ?? null;
$dbname=$_ENV['DB_TOTEM_NAME'] ?? null;
$dbuser=$_ENV['DB_TOTEM_USER'] ?? null;
$dbpassword=$_ENV['DB_TOTEM_PASS'] ?? null;

$conn_totem = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword");
if (!$conn_totem) {
   die("<br>Could not connect to DB PostgreSQL $dbname, please contact the administrator.");
}
//echo '<i class="fa-solid fa-link-slash"></i> Problemi di connessione con il totem. Alcune pagine potrebbero non funzionare <hr>';






        
// STRINGA CONNESSIONE UNIOPE

$dbhost=$_ENV['DB_PEOR_HOST'] ?? null;
$dbport=$_ENV['DB_PEOR_PORT'] ?? null;
$dbservice=$_ENV['DB_PEOR_SERVICE_NAME'] ?? null;
$dbuser=$_ENV['DB_PEOR_USER'] ?? null;
$dbpassword=$_ENV['DB_PEOR_PASS'] ?? null;


$dbstr="(DESCRIPTION = 
(ADDRESS = (PROTOCOL = TCP)(Host = $dbhost)(Port = $dbport))
(CONNECT_DATA = (SERVICE_NAME = $dbservice))
)";


//echo "<br>ok1";
// User, pwd, stringa_connessione
$oraconn = oci_connect($dbuser, $dbpassword, $dbstr);
if (!$oraconn) {
        //$e = oci_error();
        //trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        die('<br>Could not connect to DB UNIOPE Oracle, please contact the administrator.');
} /*else {
        echo "<br>ok2";
}*/









######################################################
# VAriabili varie 

$url_sit=$_ENV['URL_SIT'] ?? null;

$url_api_chiusura=$_ENV['URL_API_SIT'] ?? null.'api/piazzole/';


$url_eliminazione_percorso=$_ENV['URL_API_SIT'] ?? null."/api/percorsi/removeastapercorso/";


$titolo_app = 'APPLICATIVO SIT - PASSAGGIO A BILATERALE'; 



######################################################
#include 'ldap.php';

$ldapDomain = $_ENV['LDAP_DOMAIN'] ?? null;
//echo "LDAP Domain: " . $ldapDomain . "<br>";
#exit(); 
$ldapHost = $_ENV['LDAP_HOST'] ?? null;
$ldapPort = $_ENV['LDAP_PORT'] ?? null;



######################################################
#include 'jwt.php';
$iss = $_ENV['ISS'] ?? null;
$secret_pwd = $_ENV['SECRET_PWD'] ?? null;


?>
