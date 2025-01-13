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

require_once "tables/query_piazzole_sovr.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


 
$spreadsheet = new Spreadsheet();
$Excel_writer = new Xlsx($spreadsheet);
 
$spreadsheet->setActiveSheetIndex(0);
$activeSheet = $spreadsheet->getActiveSheet();
 




$activeSheet->setCellValue('A1', 'Id piazzola');
$activeSheet->setCellValue('B1', 'Id elemento');
$activeSheet->setCellValue('C1', 'Rif');
$activeSheet->setCellValue('D1', 'Municipio');
$activeSheet->setCellValue('E1', 'Comune');
$activeSheet->setCellValue('F1', 'Eliminata');
$activeSheet->setCellValue('G1', 'Anno');
$activeSheet->setCellValue('H1', 'Elementi al 31/12 anno preeedente');
$activeSheet->setCellValue('I1', 'Percorsi al 31/12 anno preeedente');
$activeSheet->setCellValue('J1', 'Numero ispezioni');



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
$result = pg_prepare($conn_sovr, "query_ps", $query_ps);
$result = pg_execute($conn_sovr, "query_ps", array());



$i = 2;
while($r = pg_fetch_assoc($result)) {
    //echo $r['id_piazzola'];
    $activeSheet->setCellValue('A'.$i , $r['id_piazzola']);
    $activeSheet->setCellValue('B'.$i , $r['id_elemento']);
    $activeSheet->setCellValue('C'.$i , $r['rif']);
    $activeSheet->setCellValue('D'.$i , $r['municipio']);
    $activeSheet->setCellValue('E'.$i , $r['comune']);
    $activeSheet->setCellValue('F'.$i , $r['eliminata']);
    $activeSheet->setCellValue('G'.$i , $r['anno']);
    $activeSheet->setCellValue('H'.$i , $r['elementi']);
    $activeSheet->getStyle('H'.$i)->getAlignment()->setWrapText(true);
    $activeSheet->setCellValue('I'.$i , $r['percorsi']);
    $activeSheet->getStyle('I'.$i)->getAlignment()->setWrapText(true);
    $activeSheet->setCellValue('J'.$i , $r['n_ispezioni_anno']);
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





$filename = 'report_piazzole_sovrariempimenti.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>