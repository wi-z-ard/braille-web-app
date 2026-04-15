<?php
declare(strict_types=1);

if ($method !== 'POST') Security::jsonResponse(['error' => 'Method not allowed'], 405);

Security::requireAuth();

if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) {
    Security::jsonResponse(['error' => 'Invalid CSRF token'], 403);
}

if (!Security::rateLimit('upload')) {
    Security::jsonResponse(['error' => 'Rate limit exceeded. Try again later.'], 429);
}

try {
    $file = $_FILES['file'] ?? null;
    if (!$file) Security::jsonResponse(['error' => 'No file provided'], 400);

    ['ext' => $ext] = Security::validateUpload($file);

    $hashedName = Security::hashFilename($ext);
    $destPath   = UPLOAD_PATH . '/' . $hashedName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Failed to save file.');
    }

    // Make file readable by Python service
    chmod($destPath, 0644);

    $language = Security::sanitizeString($_POST['language'] ?? 'auto');
    if (!in_array($language, ['auto', 'en', 'ar'], true)) $language = 'auto';

    $user   = Auth::current();
    $jobId  = JobQueue::create($user['id'], $hashedName, basename($file['name']), $ext, $language);
    
    ActivityLog::log('file_upload', "Uploaded: {$file['name']} ({$ext})");

    // Dispatch to Python service
    $dispatched = dispatchToPython($jobId, $hashedName, $ext, $language);
    
    error_log("BRF: Job $jobId created, dispatch result: " . ($dispatched ? 'success' : 'FAILED'));

    Security::jsonResponse([
        'success' => true,
        'job_id'  => $jobId,
        'redirect'=> BASE_PATH . "/job/{$jobId}",
    ]);

} catch (RuntimeException $e) {
    Security::jsonResponse(['error' => $e->getMessage()], 422);
}

function dispatchToPython(string $jobId, string $filename, string $fileType, string $language): bool {
    $payload = json_encode([
        'job_id'    => $jobId,
        'filename'  => $filename,
        'file_type' => $fileType,
        'language'  => $language,
    ]);

    $url = PYTHON_SERVICE_URL . '/process';
    error_log("BRF: Dispatching to $url with payload: $payload");

    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => implode("\r\n", [
            'Content-Type: application/json',
            'Authorization: Bearer ' . PYTHON_SERVICE_SECRET,
        ]),
        'content' => $payload,
        'timeout' => 5,
        'ignore_errors' => true,
    ]]);

    $response = @file_get_contents($url, false, $ctx);
    error_log("BRF: Python response: " . ($response ?: 'EMPTY/FAILED'));
    return $response !== false;
}
