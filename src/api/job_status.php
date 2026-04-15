<?php
declare(strict_types=1);

Security::requireAuth();

$jobId = Security::sanitizeString($_GET['id'] ?? '');
if (!$jobId) Security::jsonResponse(['error' => 'Missing job ID'], 400);

$user = Auth::current();
$job  = JobQueue::get($jobId, $user['id']);

if (!$job) Security::jsonResponse(['error' => 'Job not found'], 404);

Security::jsonResponse([
    'id'         => $job['id'],
    'status'     => $job['status'],
    'orig_name'  => $job['orig_name'],
    'language'   => $job['language'],
    'created_at' => $job['created_at'],
    'updated_at' => $job['updated_at'],
    'has_result' => !empty($job['result_path']),
    'error'      => $job['error'],
]);
