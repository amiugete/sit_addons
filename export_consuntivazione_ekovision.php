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
//  output buffering per non fare stampe

require_once "tables/query_consuntivazione_ekovision.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo 'Test <br>';
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$cache = new MyCustomPsr16Implementation();
echo 'OK 1<br>';

\PhpOffice\PhpSpreadsheet\Settings::setCache($cache);
echo 'OK 2<br>';

 
$spreadsheet = new Spreadsheet();
$Excel_writer = new Xlsx($spreadsheet);
 
$spreadsheet->setActiveSheetIndex(0);
$activeSheet = $spreadsheet->getActiveSheet();
 




$activeSheet->setCellValue('A1', 'Fam servizio');
$activeSheet->setCellValue('B1', 'Desc servizio');
$activeSheet->setCellValue('C1', 'UT');
$activeSheet->setCellValue('D1', 'Data pianificata');
$activeSheet->setCellValue('E1', 'Data esecuzione');
$activeSheet->setCellValue('F1', 'Cod percorso');
$activeSheet->setCellValue('G1', 'Descrizione');
$activeSheet->setCellValue('H1', 'Previsto');
$activeSheet->setCellValue('I1', 'Ora esecuzione');
$activeSheet->setCellValue('J1', 'Fascia turno');
$activeSheet->setCellValue('K1', 'FLAG serv non completato');
$activeSheet->setCellValue('L1', 'FLAG serv non effettuato');
$activeSheet->setCellValue('M1', 'Stato');
$activeSheet->setCellValue('N1', 'Id scheda Ekovision');



//oci_bind_by_name($result3, ':p1', $turno);
oci_execute($result);
$i = 2;
while($r = oci_fetch_assoc($result)) { 
    echo $i.'<br>';
    exit;
    $activeSheet->setCellValue('A'.$i , $r['FAM_SERVIZIO']);
    $activeSheet->setCellValue('B'.$i , $r['DESC_SERVIZIO']);
    $activeSheet->setCellValue('C'.$i , $r['UT']);
    $activeSheet->setCellValue('D'.$i , $r['DATA_PIANIFICATA']);
    $activeSheet->setCellValue('E'.$i , $r['DATA_ESECUZIONE']);
    $activeSheet->setCellValue('F'.$i , $r['COD_PERCORSO']);
    $activeSheet->setCellValue('G'.$i , $r['DESCRIZIONE']);
    $activeSheet->setCellValue('H'.$i , $r['PREVISTO']);
    $activeSheet->setCellValue('I'.$i , $r['ORARIO_ESECUZIONE']);
    $activeSheet->setCellValue('J'.$i , $r['FASCIA_TURNO']);
    $activeSheet->setCellValue('K'.$i , $r['FLG_SEGN_SRV_NON_COMPL']);
    $activeSheet->setCellValue('L'.$i , $r['FLG_SEGN_SRV_NON_EFFETT']);
    $activeSheet->setCellValue('M'.$i , $r['STATO']);
    $activeSheet->setCellValue('N'.$i , $r['ID_SCHEDA']);
    $i++;
}
//echo $i;
//exit;
oci_free_statement($result);
oci_close($oraconn);

// autosize
foreach (range('A', $activeSheet->getHighestColumn()) as $col) {
    $activeSheet->getColumnDimension($col)->setAutoSize(true);
 }


 // autofilter
 // definisco prima e ultima riga
 $firstrow=1;
 $lastrow=$i-1;
// set autofilter
$activeSheet->setAutoFilter("A".$firstrow.":N".$lastrow);





$filename = 'report_consuntivazione_ekovision.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>