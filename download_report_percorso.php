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
  $comando='/usr/bin/python3 /home/procedure/script_sit_amiu/report_settimanali_percorsi.py '.$id.'';
} else if ($vers=='s'){
  $comando='/usr/bin/python3 /home/procedure/script_sit_amiu/report_settimanali_percorsi.py '.$id.' sempl';
}
//echo $comando;
//echo '<br><br>';
exec($comando, $output, $retval);
if ($retval == 0) {
  // define file $mime type here
  if ($vers=='c') {
    $file_name = '/tmp/report/report_'.$id.'.xlsx';
  } else if ($vers=='s'){
    $file_name = '/tmp/report/report_'.$id.'_operatore.xlsx';
  }
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
    echo "Codice errore $retval <br>Verificare che il percorso con id $id sia presente su SIT. <br>Se il problema sussiste contattare $problemi <br><br><br>";
} 
?>
           


</div>


</div>

<?php
//srequire_once('req_bottom.php');
//require('./footer.php');
?>
