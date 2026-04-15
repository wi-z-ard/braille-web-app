<?php
declare(strict_types=1);

class Admin {

    public static function requireAdmin(): void {
        Security::requireAuth();
        $user = Auth::current();
        
        $db = Database::get();
        $stmt = $db->prepare('SELECT is_admin FROM users WHERE id=?');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        
        if (!$row || !$row['is_admin']) {
            http_response_code(403);
            die('Access denied. Admin privileges required.');
        }
    }

    public static function getAllUsers(int $limit = 100, int $offset = 0): array {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT u.*, 
                   COUNT(j.id) as total_jobs,
                   SUM(CASE WHEN j.status="done" THEN 1 ELSE 0 END) as completed_jobs
            FROM users u
            LEFT JOIN jobs j ON u.id = j.user_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ');
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function getAllJobs(int $limit = 50, int $offset = 0, ?string $status = null): array {
        $db = Database::get();
        $sql = '
            SELECT j.*, u.name as user_name, u.email as user_email
            FROM jobs j
            LEFT JOIN users u ON j.user_id = u.id
        ';
        if ($status) {
            $sql .= ' WHERE j.status = ?';
        }
        $sql .= ' ORDER BY j.created_at DESC LIMIT ? OFFSET ?';
        
        $stmt = $db->prepare($sql);
        $params = $status ? [$status, $limit, $offset] : [$limit, $offset];
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getStats(): array {
        $db = Database::get();
        
        $stats = $db->query('
            SELECT 
                COUNT(DISTINCT u.id) as total_users,
                COUNT(j.id) as total_jobs,
                SUM(CASE WHEN j.status="done" THEN 1 ELSE 0 END) as completed_jobs,
                SUM(CASE WHEN j.status="error" THEN 1 ELSE 0 END) as failed_jobs,
                SUM(CASE WHEN j.status="pending" OR j.status="processing" THEN 1 ELSE 0 END) as active_jobs
            FROM users u
            LEFT JOIN jobs j ON u.id = j.user_id
        ')->fetch();

        $recentActivity = $db->query('
            SELECT COUNT(*) as count 
            FROM jobs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ')->fetch();

        $stats['jobs_24h'] = $recentActivity['count'];
        $stats['success_rate'] = $stats['total_jobs'] > 0 
            ? round(($stats['completed_jobs'] / $stats['total_jobs']) * 100, 1) 
            : 0;

        return $stats;
    }

    public static function toggleUserStatus(int $userId): bool {
        $db = Database::get();
        $db->prepare('UPDATE users SET is_active = NOT is_active WHERE id=?')->execute([$userId]);
        return true;
    }

    public static function deleteUser(int $userId): bool {
        $db = Database::get();
        $db->prepare('DELETE FROM users WHERE id=? AND is_admin=0')->execute([$userId]);
        return true;
    }

    public static function logActivity(int $userId, string $action, ?string $details = null): void {
        $db = Database::get();
        $db->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)')
           ->execute([$userId, $action, $details, Security::clientIp()]);
    }

    public static function getActivityLogs(int $limit = 50): array {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT a.*, u.name as user_name, u.email as user_email
            FROM activity_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
