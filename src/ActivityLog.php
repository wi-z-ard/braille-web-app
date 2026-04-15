<?php
declare(strict_types=1);

class ActivityLog {
    
    public static function log(string $action, ?string $details = null, ?int $userId = null): void {
        $userId = $userId ?? Auth::current()['id'] ?? null;
        if (!$userId) return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $db = Database::get();
        $db->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)')
           ->execute([$userId, $action, $details, $ip]);
    }

    public static function getRecent(int $limit = 50): array {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT a.*, u.name as user_name, u.email as user_email 
            FROM activity_logs a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function getByUser(int $userId, int $limit = 20): array {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
