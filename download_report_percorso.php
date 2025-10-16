<?php
$id=pg_escape_string($_GET['cod']);
$vers=pg_escape_string($_GET['vers']);
?>
<?php 
//require_once('./req.php');
//require_once('./conn.php');
?> 

<?php
$output=null;
$retval=null;
if ($vers=='c') {
  $comando='/usr/bin/python3 ./py_scripts/report_settimanali_percorsi_ok.py '.$id.' compl no 0';
  $file_name = '/tmp/report/report_' . $id . '.xlsx';
  $download_name = 'report_' . $id . '.xlsx';
} else if ($vers=='s'){
  $comando='/usr/bin/python3 ./py_scripts/report_settimanali_percorsi_ok.py '.$id.' sempl no 0';
  $file_name = '/tmp/report/report_' . $id . '_operatore.xlsx';
  $download_name = 'report_' . $id . '_operatore.xlsx';
}
//echo $comando;
//echo '<br><br>';
//exit();
exec($comando, $output, $retval);
if ($retval === 0 && file_exists($file_name)) {
// Disabilita compressione e buffer
    if (function_exists('ob_end_clean')) ob_end_clean();
    header_remove('Set-Cookie');
    header_remove('X-Powered-By');
    header_remove('Pragma');

    // Imposta header binari
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $download_name . '"');
    header('Content-Length: ' . filesize($file_name));
    header('Cache-Control: must-revalidate');
    header('Expires: 0');
    header('Pragma: public');

    // Invio del file in modalità binaria
    $fp = fopen($file_name, 'rb');
    fpassthru($fp);
    fclose($fp);
    exit;

} else {
    http_response_code(500);
    echo "Errore nella generazione del report (codice $retval).<br>";
    echo implode("<br>", $output);
}  
  
/*  
if ($retval === 0 && file_exists($file_name)) {  
  // define file $mime type here
  // first, get MIME information from the file
  $finfo = finfo_open(FILEINFO_MIME_TYPE); 
  $mime =  finfo_file($finfo, $file_name);
  finfo_close($finfo);

  // send header information to browser
  header('Content-Type: '.$mime);
  if ($vers=='c') {
    header('Content-Disposition: attachment;  filename="report_'.$id.'.xlsx"');
  } else if ($vers=='s'){
    header('Content-Disposition: attachment;  filename="report_'.$id.'_operatore.xlsx"');
  }
  header('Content-Length: ' . filesize($file_name));
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

  //stream file
  ob_get_clean();
  echo file_get_contents($file_name);
  //readfile($file_name);
  ob_end_flush();


 
} else {
    echo "Codice errore $retval 
    <br>
    $output
    <br><br>Verificare che il percorso con id $id sia presente su SIT. <br>Se il problema sussiste contattare $problemi <br><br><br>";
} */

?>
           




<?php
//srequire_once('req_bottom.php');
//require('./footer.php');
?>