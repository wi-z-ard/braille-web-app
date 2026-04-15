<?php
declare(strict_types=1);

// Only accept from localhost
Security::requireInternalRequest();

if ($method !== 'POST') Security::jsonResponse(['error' => 'Method not allowed'], 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['job_id'])) {
    Security::jsonResponse(['error' => 'Invalid payload'], 400);
}

$jobId  = Security::sanitizeString($body['job_id']);
$status = in_array($body['status'] ?? '', ['done', 'error'], true) ? $body['status'] : 'error';
$result = isset($body['result_path']) ? Security::sanitizeString($body['result_path'], 512) : null;
$error  = isset($body['error']) ? Security::sanitizeString($body['error'], 512) : null;

// Validate result path is within processed dir
if ($result && !str_starts_with(realpath($result) ?: '', realpath(PROCESSED_PATH))) {
    Security::jsonResponse(['error' => 'Invalid result path'], 400);
}

JobQueue::updateStatus($jobId, $status, $result, $error);
Security::jsonResponse(['ok' => true]);
