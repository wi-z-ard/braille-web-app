<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Admin/Admin.php';

Admin::requireAdmin();

if ($method !== 'POST') Security::jsonResponse(['error' => 'Method not allowed'], 405);

$body = json_decode(file_get_contents('php://input'), true);
$userId = (int)($body['user_id'] ?? 0);

if (!$userId) Security::jsonResponse(['error' => 'Invalid user ID'], 400);

try {
    Admin::deleteUser($userId);
    Admin::logActivity(Auth::current()['id'], 'delete_user', "User ID: $userId");
    Security::jsonResponse(['success' => true]);
} catch (Exception $e) {
    Security::jsonResponse(['error' => $e->getMessage()], 500);
}
