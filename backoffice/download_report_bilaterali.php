<?php

$job_id = $_GET['job_id'] ?? '';


if (!preg_match('/^[a-f0-9]{32}$/', $job_id)) {
    http_response_code(400);
    exit;
}


$job_dir = '/tmp/report/jobs';

$status_file = $job_dir . '/' . $job_id . '.json';

$file_name = $job_dir . '/' . $job_id . '.xlsx';


if (!file_exists($status_file)) {
    http_response_code(404);
    exit;
}


$status = json_decode(
    file_get_contents($status_file),
    true
);


if (($status['status'] ?? '') !== 'ready') {
    http_response_code(409);
    exit;
}


if (!file_exists($file_name)) {
    http_response_code(404);
    exit;
}


$download_name = $status['filename'] ?? 'report.xlsx';


$finfo = finfo_open(FILEINFO_MIME_TYPE);

$mime = finfo_file(
    $finfo,
    $file_name
);

finfo_close($finfo);


header('Content-Type: ' . $mime);

header(
    'Content-Disposition: attachment; filename="' .
    basename($download_name) .
    '"'
);

header('Content-Length: ' . filesize($file_name));

header('Expires: 0');

header('Cache-Control: must-revalidate');


readfile($file_name);

exit;
?>