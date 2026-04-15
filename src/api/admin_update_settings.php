<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/Admin/Admin.php';

Admin::requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    Security::jsonResponse(['error' => 'Invalid data'], 400);
}

foreach ($data as $key => $value) {
    if (!is_numeric($value) || $value < 0) {
        Security::jsonResponse(['error' => "Invalid value for {$key}"], 400);
    }
    Settings::set($key, $value);
}

Security::jsonResponse(['success' => true]);
