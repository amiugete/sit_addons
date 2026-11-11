<?php
require 'vendor/autoload.php';

/**
 * Genera un barcode Code128 per dati numerici, lasciando che la libreria
 * scelga automaticamente il subset piu' efficiente (C per le cifre, con
 * switch a B solo se il numero di cifre e' dispari).
 *
 * IMPORTANTE: usare la classe GENERICA TypeCode128, NON TypeCode128B.
 * TypeCode128B forza la codifica a 1 carattere per simbolo anche per
 * dati puramente numerici, producendo barcode ~65% piu' larghi a parita'
 * di leggibilita' (vedi confronto sotto).
 */

$code = "2026082009970027011000123"; // 25 cifre
$code2 = "202608200997006201999419"; // 24 cifre


// ---- MODO CORRETTO: subset scelto automaticamente (C per i numeri) ----
$barcode = (new Picqer\Barcode\Types\TypeCode128())->getBarcode($code);
$barcode2 = (new Picqer\Barcode\Types\TypeCode128())->getBarcode($code2);

$renderer = new Picqer\Barcode\Renderers\SvgRenderer();
?>
<!DOCTYPE html>
<html>
<body>
  <h3>Test barcode - larghezza target 10cm</h3>
  <div style="width:100mm; border:1px dashed #ccc; padding:5px;">
    <?php echo $code;?>    
    <br>
    <?php echo $renderer->render($barcode, 370, 60); ?>
  </div>
  <br><br><br><br><br><br><br><br>
  <hr>
  <br><br><br><br><br><br><br><br>
    <div style="width:100mm; border:1px dashed #ccc; padding:5px;">
    <?php echo $code2;?>    
    <br>
    <?php echo $renderer->render($barcode2, 370, 60); ?>
  </div>
<br><br><br><br><br><br><br><br>
  <hr>
<br><br><br><br><br><br><br><br>
  <h3>Simulazione stampa 2-per-foglio (scala 50%)</h3>
  <div style="width:50mm; border:1px dashed #ccc; padding:5px;">
    <?php echo $code;?>    
    <br>
    <?php echo $renderer->render($barcode, 185, 30); ?>
  </div>
  <br><br><br><br><br><br><br><br>
  <hr>
  <br><br><br><br><br><br><br><br>
  <div style="width:50mm; border:1px dashed #ccc; padding:5px;">
    <?php echo $code2;?>    
    <br>
    <?php echo $renderer->render($barcode2, 185, 30); ?>
  </div>
</body>
</html>