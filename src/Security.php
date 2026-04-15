<?php
declare(strict_types=1);

class Security {

    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function rateLimit(string $action, int $limit = RATE_LIMIT_UPLOADS): bool {
        $ip  = self::clientIp();
        $now = time();
        $db  = Database::get();

        $row = $db->prepare('SELECT hits, window_start FROM rate_limits WHERE ip=? AND action=?');
        $row->execute([$ip, $action]);
        $data = $row->fetch();

        if (!$data || ($now - $data['window_start']) > RATE_LIMIT_WINDOW) {
            $db->prepare('INSERT INTO rate_limits (ip, action, hits, window_start) VALUES (?,?,1,?) 
                          ON DUPLICATE KEY UPDATE hits=1, window_start=?')
               ->execute([$ip, $action, $now, $now]);
            return true;
        }

        if ($data['hits'] >= $limit) return false;

        $db->prepare('UPDATE rate_limits SET hits=hits+1 WHERE ip=? AND action=?')
           ->execute([$ip, $action]);
        return true;
    }

    public static function validateUpload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error code: ' . $file['error']);
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('File exceeds 50MB limit.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('File type not allowed.');
        }

        // Real MIME check via finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        // TXT files can have various MIME types
        $allowedMimes = ALLOWED_MIME_TYPES;
        if ($ext === 'txt') {
            $allowedMimes[] = 'text/html'; // some systems report this
            $allowedMimes[] = 'application/octet-stream';
        }

        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException("MIME type '$mime' not permitted.");
        }

        // Ensure no PHP/executable content in file
        $content = file_get_contents($file['tmp_name'], false, null, 0, 512);
        if (preg_match('/<\?php|<script|eval\s*\(/i', $content)) {
            throw new RuntimeException('Malicious content detected.');
        }

        return ['ext' => $ext, 'mime' => $mime];
    }

    public static function sanitizeString(string $input, int $maxLen = 255): string {
        return htmlspecialchars(
            substr(trim(strip_tags($input)), 0, $maxLen),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }

    public static function clientIp(): string {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return filter_var(explode(',', $_SERVER[$key])[0], FILTER_VALIDATE_IP) ?: '0.0.0.0';
            }
        }
        return '0.0.0.0';
    }

    public static function hashFilename(string $ext): string {
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    public static function jsonResponse(array $data, int $code = 200): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    public static function requireInternalRequest(): void {
        $secret = $_SERVER['HTTP_X_INTERNAL_SECRET'] ?? '';
        if (!hash_equals(PYTHON_SERVICE_SECRET, $secret)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
