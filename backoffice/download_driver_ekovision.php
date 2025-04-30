<?php
$data_start = explode("-", $_POST['daterange'])[0]; 
$data_end = explode("-", $_POST['daterange'])[1];
$tipo_report = $_POST['tipo_report'];
$email = $_POST['email'];
?>
<?php 
//require_once('./req.php');
//require_once('./conn.php');
?> 

<?php
$output=null;
$retval=null;

#echo $_SERVER['REQUEST_URI'];
#echo "<br>";
#echo $_SERVER['SCRIPT_FILENAME'];
#echo "<br>";
$comando='/usr/bin/python3 ../py_scripts/export_driver_ekovision.py '.$data_start.' '.$data_end.' '.$tipo_report.' '.$email.'';

#echo $comando;
#exit();

//echo '<br><br>';
exec($comando, $output, $retval);
if ($retval == 0) {
  $file_name = '/tmp/driver_eko/driver_ekovision.xlsx';
  // first, get MIME information from the file
  $finfo = finfo_open(FILEINFO_MIME_TYPE); 
  $mime =  finfo_file($finfo, $file_name);
  finfo_close($finfo);

  // send header information to browser
  header('Content-Type: '.$mime);
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Description: File Transfer");
  header("Access-Control-Allow-Origin: *");
  header('Content-Disposition: attachment;  filename="driver_ekovision_'.date('YmdHis').'.xlsx"');
  header("Content-Transfer-Encoding: binary");
  header('Content-Length: ' . filesize($file_name));
  header('Expires: 0');
  header("Pragma: public"); // required


  //stream file
  ob_get_clean();
  echo file_get_contents($file_name);
  //readfile($file_name);
  ob_end_flush();
  //readfile($file_name);//important this line
  //unlink($file_name);

  //die();
  http_response_code(200);
 
} else {
    http_response_code(400);
    echo "Codice errore $retval <br>";
    print_r($output);
} 
?>
           


</div>


</div>

<?php
//srequire_once('req_bottom.php');
//require('./footer.php');
?>
