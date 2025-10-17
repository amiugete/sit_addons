<?php

$output=null;
$retval=null;

$comando='/usr/bin/python3 ../py_scripts/report_settimanali_percorsi_ok.py 0 all_bilaterale no 0';
$file_name = '/tmp/report/report_bilaterali.xlsx';
$download_name = 'report_bilaterali.xlsx';

//echo $comando;
//echo '<br><br>';
//exit();
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