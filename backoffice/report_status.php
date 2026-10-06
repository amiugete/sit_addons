<?php

header('Content-Type: application/json');

$job_id = $_GET['job_id'] ?? '';


if (!preg_match('/^[a-f0-9]{32}$/', $job_id)) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Job ID non valido'
    ]);

    exit;
}


$status_file = '/tmp/report/jobs/' . $job_id . '.json';


if (!file_exists($status_file)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Job non trovato'
    ]);

    exit;
}


readfile($status_file);

exit;
?>