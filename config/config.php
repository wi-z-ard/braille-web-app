<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('BASE_PATH', '/brf-helper/public'); // Set to '' if running at domain root
define('STORAGE_PATH', APP_ROOT . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('PROCESSED_PATH', STORAGE_PATH . '/processed');
define('JOBS_PATH', STORAGE_PATH . '/jobs');
define('TEMP_PATH', STORAGE_PATH . '/temp');

define('PYTHON_SERVICE_URL', 'http://127.0.0.1:8001');
define('PYTHON_SERVICE_SECRET', getenv('PYTHON_SECRET') ?: 'change-this-in-production-32chars');

define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('RATE_LIMIT_UPLOADS', 10);           // per hour per IP
define('RATE_LIMIT_WINDOW', 3600);

define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
]);
define('ALLOWED_EXTENSIONS', ['pdf', 'docx', 'txt']);

define('SESSION_LIFETIME', 7200);
define('CSRF_TOKEN_LENGTH', 32);

// Load .env file
if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Database - MySQL
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'braille_bridge');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
