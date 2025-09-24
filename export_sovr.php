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
 


/*
$activeSheet->setCellValue('A1', 'Id ispezione');
$activeSheet->setCellValue('B1', 'Data ora verifica');
$activeSheet->setCellValue('C1', 'Comune');
$activeSheet->setCellValue('D1', 'Cod Istat');
$activeSheet->setCellValue('E1', 'Piazzola');
$activeSheet->setCellValue('F1', 'Zona');
$activeSheet->setCellValue('G1', 'Ut');
$activeSheet->setCellValue('H1', 'Quartiere');
$activeSheet->setCellValue('I1', 'Id segnalazione');
$activeSheet->setCellValue('J1', 'Data ora segnalazione');
$activeSheet->setCellValue('K1', 'Contenitori presenti su SIT');
$activeSheet->setCellValue('L1', 'Ispezione eseguita da');
$activeSheet->setCellValue('M1', 'Contenitori ispezionati');
$activeSheet->setCellValue('N1', 'Contenitori sovrariempiti');
$activeSheet->setCellValue('O1', 'Dettagli sovrariempiti');
$activeSheet->setCellValue('P1', 'Dettagli svuotamenti');
$activeSheet->setCellValue('Q1', 'Congruenza SIT');
$activeSheet->setCellValue('R1', 'Indicatore');
*/

function colonnaLettera($indice) {
    $lettera = '';
    while ($indice >= 0) {
        $lettera = chr($indice % 26 + 65) . $lettera;
        $indice = floor($indice / 26) - 1;
    }
    return $lettera;
}

$intestazioni = [
    'Id ispezione',
    'Data ora verifica',
    'Comune',
    'Cod Istat',
    'Piazzola',
    'Zona',
    'Ut',
    'Quartiere',
    'Id segnalazione',
    'Data ora segnalazione',
    'Contenitori presenti su SIT',
    'Ispezione eseguita da',
    'Contenitori ispezionati',
    'Contenitori sovrariempiti',
    'Dettagli sovrariempiti',
    'Dettagli svuotamenti',
    'Congruenza SIT',
    'Indicatore', 
    'Num ispezioni effettuate',
    'Num ispezioni previste'
];

foreach ($intestazioni as $i => $label) {
    $colonna = colonnaLettera($i); // A, B, C, ..., R
    $activeSheet->setCellValue($colonna . '1', $label);
}


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

/*
$i = 2;
while($r = pg_fetch_assoc($result)) {
    //echo $r['id_piazzola'];
    $activeSheet->setCellValue('A'.$i , $r['id_ispezione']);
    $activeSheet->setCellValue('B'.$i , $r['data_ora_verifica']);
    $activeSheet->setCellValue('C'.$i , $r['descr_comune']);
    $activeSheet->setCellValue('D'.$i , $r['cod_istat']);
    $activeSheet->setCellValue('E'.$i , $r['piazzola']);
    $activeSheet->setCellValue('F'.$i , $r['zona']);
    $activeSheet->setCellValue('G'.$i , $r['ut']);
    $activeSheet->setCellValue('H'.$i , $r['quartiere']);
    $activeSheet->setCellValue('I'.$i , $r['id_segnalazione']);
    $activeSheet->setCellValue('J'.$i , $r['data_ora_segnalazione']);
    $activeSheet->setCellValue('K'.$i , $r['contenitori_presenti_su_sit']);
    $activeSheet->setCellValue('L'.$i , $r['ispezione_eseguita_da']);
    $activeSheet->setCellValue('M'.$i , $r['contenitori_ispezionati']);
    $activeSheet->setCellValue('N'.$i , $r['contenitori_sovrariempiti']);
    $activeSheet->setCellValue('O'.$i , $r['dettagli_sovrariempiti']);
    $activeSheet->setCellValue('P'.$i , $r['dettagli_svuotamenti']);
    $activeSheet->setCellValue('Q'.$i , $r['congruenza_sit']);
    $activeSheet->setCellValue('R'.$i , $r['indicatore']);
    $i++;
}
    */
//exit;

$campi = [
  'id_ispezione',
  'data_ora_verifica',
  'descr_comune',
  'cod_istat',
  'piazzola',
  'zona',
  'ut',
  'quartiere',
  'id_segnalazione',
  'data_ora_segnalazione',
  'contenitori_presenti_su_sit',
  'ispezione_eseguita_da',
  'contenitori_ispezionati',
  'contenitori_sovrariempiti',
  'dettagli_sovrariempiti',
  'dettagli_svuotamenti',
  'congruenza_sit',
  'indicatore',
  'num_ispezioni_effettuate',
  'num_ispezioni_previste'
];



$i = 2;
while ($r = pg_fetch_assoc($result)) {
    foreach ($campi as $j => $nome_campo) {
        $colonna = colonnaLettera($j); // A, B, ..., R
        $activeSheet->setCellValue($colonna . $i, $r[$nome_campo]);
    }
    $i++;
}


// autosize
/*foreach (range('A', $activeSheet->getHighestColumn()) as $col) {
    $activeSheet->getColumnDimension($col)->setAutoSize(true);
 }*/
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($activeSheet->getHighestColumn());

for ($col = 1; $col <= $lastCol; $col++) {
    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $activeSheet->getColumnDimension($letter)->setAutoSize(true);
}


 // autofilter
 // definisco prima e ultima riga
 $firstrow=1;
 $lastrow=$i-1;
// set autofilter
//$activeSheet->setAutoFilter("A".$firstrow.":Q".$lastrow);

// supponendo che tu abbia già l’array $campi usato per le intestazioni
$lastColIndex = count($campi) - 1;
$lastCol = colonnaLettera($lastColIndex);

$activeSheet->setAutoFilter("A{$firstRow}:{$lastCol}{$lastRow}");




$filename = 'report_sovrariempimenti.xlsx';
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='. $filename);
header('Cache-Control: max-age=0');
ob_end_clean();
$Excel_writer->save('php://output');
//require_once('./req_bottom.php');
?>