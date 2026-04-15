<?php
declare(strict_types=1);

class JobQueue {

    public static function create(int $userId, string $filename, string $origName, string $fileType, string $language = 'auto'): string {
        $id = self::generateId();
        $db = Database::get();

        $db->prepare('INSERT INTO jobs (id, user_id, filename, orig_name, status, language, file_type) VALUES (?,?,?,?,?,?,?)')
           ->execute([$id, $userId, $filename, $origName, 'pending', $language, $fileType]);

        // Write job manifest for Python service
        file_put_contents(
            JOBS_PATH . "/{$id}.json",
            json_encode([
                'id'        => $id,
                'filename'  => $filename,
                'file_type' => $fileType,
                'language'  => $language,
                'status'    => 'pending',
                'created_at'=> time(),
            ], JSON_PRETTY_PRINT)
        );

        return $id;
    }

    public static function get(string $id, int $userId): ?array {
        $db   = Database::get();
        $stmt = $db->prepare('SELECT * FROM jobs WHERE id=? AND user_id=? LIMIT 1');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function listForUser(int $userId, int $limit = 20): array {
        $db   = Database::get();
        $stmt = $db->prepare('SELECT * FROM jobs WHERE user_id=? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(string $id, string $status, ?string $resultPath = null, ?string $error = null): void {
        $db = Database::get();
        $db->prepare('UPDATE jobs SET status=?, result_path=?, error=?, updated_at=CURRENT_TIMESTAMP WHERE id=?')
           ->execute([$status, $resultPath, $error, $id]);
    }

    public static function getByIdInternal(string $id): ?array {
        $stmt = Database::get()->prepare('SELECT * FROM jobs WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private static function generateId(): string {
        return sprintf('%s-%s', date('Ymd'), bin2hex(random_bytes(8)));
    }
}
