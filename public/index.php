<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Security.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Settings.php';
require_once __DIR__ . '/../src/ActivityLog.php';
require_once __DIR__ . '/../src/Jobs/JobQueue.php';

Security::startSession();

// Strip base path if running in subdirectory
$basePath = '/brf-helper/public';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}
$method = $_SERVER['REQUEST_METHOD'];

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; style-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://fonts.googleapis.com; script-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com; img-src \'self\' data:; font-src \'self\' https://fonts.gstatic.com; connect-src \'self\'');

// Router
match(true) {
    $uri === '/'                                    => require __DIR__ . '/../public/pages/landing.php',
    $uri === '/upload'                              => require __DIR__ . '/../public/pages/upload.php',
    $uri === '/convert'                             => require __DIR__ . '/../public/pages/convert.php',
    $uri === '/dashboard'                           => require __DIR__ . '/../public/pages/dashboard.php',
    $uri === '/admin'                               => require __DIR__ . '/../public/pages/admin.php',
    $uri === '/login'                               => require __DIR__ . '/../public/pages/login.php',
    $uri === '/register'                            => require __DIR__ . '/../public/pages/register.php',
    str_starts_with($uri, '/job/')                  => require __DIR__ . '/../public/pages/job.php',
    $uri === '/api/upload'                          => require __DIR__ . '/../src/api/upload.php',
    $uri === '/api/convert-text'                    => require __DIR__ . '/../src/api/convert_text.php',
    $uri === '/api/job-status'                      => require __DIR__ . '/../src/api/job_status.php',
    $uri === '/api/download'                        => require __DIR__ . '/../src/api/download.php',
    $uri === '/api/auth/login'                      => require __DIR__ . '/../src/api/auth_login.php',
    $uri === '/api/auth/register'                   => require __DIR__ . '/../src/api/auth_register.php',
    $uri === '/api/auth/logout'                     => require __DIR__ . '/../src/api/auth_logout.php',
    $uri === '/api/admin/toggle-user'               => require __DIR__ . '/../src/api/admin_toggle_user.php',
    $uri === '/api/admin/delete-user'               => require __DIR__ . '/../src/api/admin_delete_user.php',
    $uri === '/api/admin/update-settings'           => require __DIR__ . '/../src/api/admin_update_settings.php',
    $uri === '/api/internal/job-update'             => require __DIR__ . '/../src/api/internal_job_update.php',
    default                                         => (function() { http_response_code(404); require __DIR__ . '/../public/pages/404.php'; })(),
};
