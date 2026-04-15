<?php
declare(strict_types=1);

Security::requireAuth();

$jobId  = Security::sanitizeString($_GET['id'] ?? '');
$format = Security::sanitizeString($_GET['format'] ?? 'brf');

if (!$jobId || !in_array($format, ['brf', 'unicode'], true)) {
    Security::jsonResponse(['error' => 'Invalid parameters'], 400);
}

$user = Auth::current();
$job  = JobQueue::get($jobId, $user['id']);

if (!$job || $job['status'] !== 'done' || empty($job['result_path'])) {
    Security::jsonResponse(['error' => 'Result not available'], 404);
}

$filename = $format === 'brf' ? 'output.brf' : 'output.txt';
$filepath = $job['result_path'] . '/' . $filename;

if (!file_exists($filepath) || !str_starts_with(realpath($filepath), realpath(PROCESSED_PATH))) {
    Security::jsonResponse(['error' => 'File not found'], 404);
}

$downloadName = pathinfo($job['orig_name'], PATHINFO_FILENAME) . '.' . $format;

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
header('Content-Length: ' . filesize($filepath));
header('X-Content-Type-Options: nosniff');
readfile($filepath);
exit;
