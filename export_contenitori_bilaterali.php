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

require_once "tables/query_contenitori_bilaterali.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


 
$spreadsheet = new Spreadsheet();
$Excel_writer = new Xlsx($spreadsheet);
 
$spreadsheet->setActiveSheetIndex(0);
$activeSheet = $spreadsheet->getActiveSheet();
 




$activeSheet->setCellValue('A1', 'Id piazzola');
$activeSheet->setCellValue('B1', 'Piazzola');
$activeSheet->setCellValue('C1', 'Municipio');
$activeSheet->setCellValue('D1', 'Quartiere');
$activeSheet->setCellValue('E1', 'Frazione rifiuto');
$activeSheet->setCellValue('F1', 'Targa contenitore');
$activeSheet->setCellValue('G1', 'Volume');
$activeSheet->setCellValue('H1', 'Data ora aggiornamento');
$activeSheet->setCellValue('I1', 'Riempimentp');
$activeSheet->setCellValue('J1', 'Batteria');
$activeSheet->setCellValue('K1', 'Batteria bocchetta');
$activeSheet->setCellValue('L1', 'Data e ora ultimo svuotamento');
$activeSheet->setCellValue('M1', 'Riempimento ultimo svuotamento');
$activeSheet->setCellValue('N1', 'Media conferimento al giorno');
$activeSheet->setCellValue('O1', 'Percorsi');



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
    $result = pg_execute($conn, "my_query", array($_GET['ut']));  
} else {
    $result = pg_execute($conn, "my_query", array());
}


$i = 2;
while($r = pg_fetch_assoc($result)) {
    //echo $r['id_piazzola'];
    $activeSheet->setCellValue('A'.$i , $r['id_piazzola']);
    $activeSheet->setCellValue('B'.$i , $r['indirizzo']);
    $activeSheet->setCellValue('C'.$i , $r['municipio']);
    $activeSheet->setCellValue('D'.$i , $r['quartiere']);
    $activeSheet->setCellValue('E'.$i , $r['frazione']);
    $activeSheet->setCellValue('F'.$i , $r['targa_contenitore']);
    $activeSheet->setCellValue('G'.$i , $r['volume_contenitore']);
    $activeSheet->setCellValue('H'.$i , $r['data_ultimo_agg']);
    $activeSheet->setCellValue('I'.$i , $r['val_riemp']);
    $activeSheet->setCellValue('J'.$i , $r['val_bat_elettronica']);
    $activeSheet->setCellValue('K'.$i , $r['val_bat_bocchetta']);
    $activeSheet->setCellValue('L'.$i , $r['data_ora_last_sv']);
    $activeSheet->setCellValue('M'.$i , $r['riempimento_svuotamento']);
    $activeSheet->setCellValue('N'.$i , $r['media_conf_giorno']);
    $activeSheet->setCellValue('O'.$i , $r['percorsi']);
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
$activeSheet->setAutoFilter("A".$firstrow.":O".$lastrow);





$filename = 'report_contenitori_bilaterali.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>