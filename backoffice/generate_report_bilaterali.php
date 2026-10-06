<?php

$job_id = $argv[1] ?? '';
$report = $argv[2] ?? '';


// Validazione job ID
if (!preg_match('/^[a-f0-9]{32}$/', $job_id)) {
    exit(1);
}


$job_dir = '/tmp/report/jobs';

$status_file = $job_dir . '/' . $job_id . '.json';

$file_job = $job_dir . '/' . $job_id . '.xlsx';


$venv_path = __DIR__ . '/../py_scripts/venv/bin/python';

$python_run_script = __DIR__ . '/../py_scripts/run.py';

$python_argv = 'report_settimanali_percorsi_ok';


// --------------------------------------------------------
// Determino codice e nome download
// --------------------------------------------------------

if ($report == 'all') {

    $codice = '0';
    $download_name = 'report_bilaterali.xlsx';

} elseif ($report == '200301') {

    $codice = '200301';
    $download_name = 'report_bilaterali_rsu.xlsx';

} elseif ($report == '150106') {

    $codice = '150106';
    $download_name = 'report_bilaterali_multi.xlsx';

} elseif ($report == '200101') {

    $codice = '200101';
    $download_name = 'report_bilaterali_carta.xlsx';

} elseif ($report == '200108') {

    $codice = '200108';
    $download_name = 'report_bilaterali_org.xlsx';

} else {

    file_put_contents(
        $status_file,
        json_encode([
            'status' => 'error',
            'message' => 'Tipo report non valido'
        ])
    );

    exit(1);
}


// --------------------------------------------------------
// Costruzione comando Python
// --------------------------------------------------------

$comando = sprintf(
    '%s %s %s %s all_bilaterale no 0 %s',
    escapeshellarg($venv_path),
    escapeshellarg($python_run_script),
    escapeshellarg($python_argv),
    escapeshellarg($codice),
    escapeshellarg($file_job)
);


$output = [];
$retval = null;


// --------------------------------------------------------
// Lancio Python
// --------------------------------------------------------

exec($comando . ' 2>&1', $output, $retval);


// --------------------------------------------------------
// Controllo risultato
// --------------------------------------------------------

if ($retval == 0 && file_exists($file_job)) {

    file_put_contents(
        $status_file,
        json_encode([
            'status' => 'ready',
            'filename' => $download_name
        ])
    );

} else {

    file_put_contents(
        $status_file,
        json_encode([
            'status' => 'error',
            'message' => 'Errore durante la generazione del report',
            'retval' => $retval,
            'output' => $output
        ])
    );
}

exit;
?>