<?php
declare(strict_types=1);

if ($method !== 'POST') Security::jsonResponse(['error' => 'Method not allowed'], 405);
if (!Security::verifyCsrf($_POST['csrf_token'] ?? '')) Security::jsonResponse(['error' => 'CSRF error'], 403);
if (!Security::rateLimit('login', 5)) Security::jsonResponse(['error' => 'Too many attempts'], 429);

try {
    Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '');
    Security::jsonResponse(['success' => true, 'redirect' => BASE_PATH . '/dashboard']);
} catch (RuntimeException $e) {
    Security::jsonResponse(['error' => $e->getMessage()], 401);
}
