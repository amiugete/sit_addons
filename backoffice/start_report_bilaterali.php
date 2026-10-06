<?php

header('Content-Type: application/json');

$report = $_POST['report'] ?? '';

$report_validi = [
    'all',
    '200301',
    '150106',
    '200101',
    '200108'
];

if (!in_array($report, $report_validi, true)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Tipo report non valido'
    ]);

    exit;
}


// Generazione ID univoco del job
$job_id = bin2hex(random_bytes(16));


// Directory contenente i job
$job_dir = '/tmp/report/jobs';

if (!is_dir($job_dir)) {
    mkdir($job_dir, 0775, true);
}



// ============================================================
// PULIZIA JOB VECCHI
// ============================================================

$max_age = 24 * 60 * 60; // 24 ore
$now = time();

foreach (glob($job_dir . '/*') as $file) {

    if (
        is_file($file) &&
        ($now - filemtime($file)) > $max_age
    ) {
        unlink($file);
    }
}


// ============================================================
// PULIZIA LOG VECCHI
// ============================================================

$log_dir = '/tmp/report_settimanali_ok/log';

if (is_dir($log_dir)) {
    foreach (glob($log_dir . '/*.log') as $file) {

    if ( is_file($file) && ($now - filemtime($file)) > $max_age) {
        unlink($file);
    }
    }
}




// Creo lo stato iniziale
$status_file = $job_dir . '/' . $job_id . '.json';

file_put_contents(
    $status_file,
    json_encode([
        'status' => 'running',
        'report' => $report
    ])
);


// Worker PHP
$worker = __DIR__ . '/generate_report_bilaterali.php';


// Avvio worker in background
$command = sprintf(
    'php %s %s %s > /dev/null 2>&1 &',
    escapeshellarg($worker),
    escapeshellarg($job_id),
    escapeshellarg($report)
);

exec($command);


// Risposta immediata al browser
echo json_encode([
    'success' => true,
    'job_id' => $job_id
]);

exit;
?>