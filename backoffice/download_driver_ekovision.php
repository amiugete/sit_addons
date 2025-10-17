<?php
// download_driver_ekovision.php
session_start();

// Recupera dati dal form
if (!isset($_POST['daterange'], $_POST['tipo_report'], $_POST['email'])) {
    http_response_code(400);
    echo "Parametri mancanti.";
    exit;
}

$output=null;
$retval=null;


$data_start = explode("-", $_POST['daterange'])[0]; 
$data_end = explode("-", $_POST['daterange'])[1];
$tipo_report = $_POST['tipo_report'];
$email = $_POST['email'];


if ($tipo_report==1) {
  $desc_file='ID_COGE';
} else if ($tipo_report==2) {
  $desc_file='ID_SERVIZIO';    
} else if ($tipo_report==3){
  $desc_file='ID_PERCORSO';  
}    


#echo $_SERVER['REQUEST_URI'];
#echo "<br>";
#echo $_SERVER['SCRIPT_FILENAME'];
#echo "<br>";


//$comando='/usr/bin/python3 ../py_scripts/export_driver_ekovision.py '.$data_start.' '.$data_end.' '.$tipo_report.' '.$email.'';

$python_script = __DIR__ . '/../py_scripts/export_driver_ekovision.py';
$file_name = "/tmp/driver_eko/driver_ekovision_{$desc_file}.xlsx";
$download_name = "report_{$desc_file}_".str_replace("/", "",$data_start)."_".str_replace("/", "",$data_end).".xlsx";





$comando = sprintf(
    '/usr/bin/python3 %s %s %s %s %s',
    escapeshellarg($python_script),
    escapeshellarg($data_start),
    escapeshellarg($data_end),
    escapeshellarg($tipo_report),
    escapeshellarg($email)
);



exec($comando. ' 2>&1', $output, $retval);

// Se tutto OK, avvia il download
if ($retval == 0) {
    require_once('./download_excel.php');
} else {
    http_response_code(500);
    echo "❌ Errore durante la generazione del report.<br>";
    echo "Codice di ritorno: $retval<br>";
    echo "Comando eseguito: <pre>$comando</pre><br>";
    echo "Output completo:<br><pre>" . implode("\n", $output) . "</pre>";

}
exit;
?>


        
