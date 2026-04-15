<?php
declare(strict_types=1);

if ($method !== 'POST') Security::jsonResponse(['error' => 'Method not allowed'], 405);
if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) Security::jsonResponse(['error' => 'CSRF error'], 403);
if (!Security::rateLimit('register', 3)) Security::jsonResponse(['error' => 'Too many attempts'], 429);

try {
    $id = Auth::register($_POST['email'] ?? '', $_POST['password'] ?? '', $_POST['name'] ?? '');
    Auth::login($_POST['email'], $_POST['password']);
    Security::jsonResponse(['success' => true, 'redirect' => BASE_PATH . '/upload']);
} catch (RuntimeException $e) {
    Security::jsonResponse(['error' => $e->getMessage()], 422);
}
