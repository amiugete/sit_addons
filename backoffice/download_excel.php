<?php
/**
 * download_excel.php
 * 
 * Script riutilizzabile per servire un file Excel generato da uno script Python.
 * Si aspetta che la variabile $file_name (percorso completo) sia definita
 * dallo script chiamante.
 * 
 * Opzionalmente puoi anche definire:
 *  - $download_name → nome del file da scaricare (es: report.xlsx)
 */

// Controlla che il file sia definito ed esista
if (!isset($file_name) || !file_exists($file_name)) {
    http_response_code(404);
    echo "❌ Errore: file non trovato o non generato correttamente.";
    exit;
}

// Determina il nome per il download
if (!isset($download_name)) {
    $download_name = basename($file_name);
}

// Determina il MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file_name);
finfo_close($finfo);

// Forza Content-Type su Excel se non riconosciuto
if (strpos($mime, 'officedocument.spreadsheetml') === false) {
    $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
}

// Pulisce eventuali buffer attivi
if (ob_get_length()) {
    ob_end_clean();
}

// Imposta gli header per il download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $download_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_name));

// Legge il file e lo invia al browser
readfile($file_name);

// Opzionale: elimina il file temporaneo dopo il download
unlink($file_name);

exit;
?>