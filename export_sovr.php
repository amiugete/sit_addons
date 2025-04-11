<?php
ob_start();
require_once('./req.php');

//the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}



require_once "vendor/autoload.php";

require_once "tables/query_report_sovr.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


 
$spreadsheet = new Spreadsheet();
$Excel_writer = new Xlsx($spreadsheet);
 
$spreadsheet->setActiveSheetIndex(0);
$activeSheet = $spreadsheet->getActiveSheet();
 



$activeSheet->setCellValue('A1', 'Id ispezione');
$activeSheet->setCellValue('B1', 'Data ora verifica');
$activeSheet->setCellValue('C1', 'Comune');
$activeSheet->setCellValue('D1', 'Piazzola');
$activeSheet->setCellValue('E1', 'Zona');
$activeSheet->setCellValue('F1', 'Ut');
$activeSheet->setCellValue('G1', 'Quartiere');
$activeSheet->setCellValue('H1', 'Id segnalazione');
$activeSheet->setCellValue('I1', 'Data ora segnalazione');
$activeSheet->setCellValue('J1', 'Contenitori presenti su SIT');
$activeSheet->setCellValue('K1', 'Ispezione eseguita da');
$activeSheet->setCellValue('L1', 'Contenitori ispezionati');
$activeSheet->setCellValue('M1', 'Contenitori sovrariempiti');
$activeSheet->setCellValue('N1', 'Dettagli sovrariempiti');
$activeSheet->setCellValue('O1', 'Dettagli svuotamenti');
$activeSheet->setCellValue('P1', 'Congruenza SIT');
$activeSheet->setCellValue('Q1', 'Indicatore');


/*$query = $db->query("SELECT * FROM products");
 
if($query->num_rows > 0) {
    $i = 2;
    while($row = $query->fetch_assoc()) {
        $activeSheet->setCellValue('A'.$i , $row['product_name']);
        $activeSheet->setCellValue('B'.$i , $row['product_sku']);
        $activeSheet->setCellValue('C'.$i , $row['product_price']);
        $i++;
    }
}*/

//echo $query;
//exit;
if($_GET['ut']) {
    $result = pg_execute($conn_sovr, "report_sovr", array($_GET['ut']));  
} else {
    $result = pg_execute($conn_sovr, "report_sovr", array());
}


$i = 2;
while($r = pg_fetch_assoc($result)) {
    //echo $r['id_piazzola'];
    $activeSheet->setCellValue('A'.$i , $r['id_ispezione']);
    $activeSheet->setCellValue('B'.$i , $r['data_ora_verifica']);
    $activeSheet->setCellValue('C'.$i , $r['descr_comune']);
    $activeSheet->setCellValue('D'.$i , $r['piazzola']);
    $activeSheet->setCellValue('E'.$i , $r['zona']);
    $activeSheet->setCellValue('F'.$i , $r['ut']);
    $activeSheet->setCellValue('G'.$i , $r['quartiere']);
    $activeSheet->setCellValue('H'.$i , $r['id_segnalazione']);
    $activeSheet->setCellValue('I'.$i , $r['data_ora_segnalazione']);
    $activeSheet->setCellValue('J'.$i , $r['contenitori_presenti_su_sit']);
    $activeSheet->setCellValue('K'.$i , $r['ispezione_eseguita_da']);
    $activeSheet->setCellValue('L'.$i , $r['contenitori_ispezionati']);
    $activeSheet->setCellValue('M'.$i , $r['contenitori_sovrariempiti']);
    $activeSheet->setCellValue('N'.$i , $r['dettagli_sovrariempiti']);
    $activeSheet->setCellValue('O'.$i , $r['dettagli_svuotamenti']);
    $activeSheet->setCellValue('P'.$i , $r['congruenza_sit']);
    $activeSheet->setCellValue('Q'.$i , $r['indicatore']);
    $i++;
}
//exit;


// autosize
foreach (range('A', $activeSheet->getHighestColumn()) as $col) {
    $activeSheet->getColumnDimension($col)->setAutoSize(true);
 }


 // autofilter
 // definisco prima e ultima riga
 $firstrow=1;
 $lastrow=$i-1;
// set autofilter
$activeSheet->setAutoFilter("A".$firstrow.":Q".$lastrow);





$filename = 'report_sovrariempimenti.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>