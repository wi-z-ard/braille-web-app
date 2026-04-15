<?php
declare(strict_types=1);

class Settings {
    private static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed {
        self::loadCache();
        return self::$cache[$key] ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int {
        return (int) self::get($key, $default);
    }

    public static function set(string $key, mixed $value): void {
        $db = Database::get();
        $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                      ON DUPLICATE KEY UPDATE setting_value=?')
           ->execute([$key, (string)$value, (string)$value]);
        self::$cache = null; // invalidate cache
    }

    public static function getAll(): array {
        $db = Database::get();
        $stmt = $db->query('SELECT setting_key, setting_value, description FROM settings ORDER BY setting_key');
        return $stmt->fetchAll();
    }

    private static function loadCache(): void {
        if (self::$cache !== null) return;
        
        $db = Database::get();
        $stmt = $db->query('SELECT setting_key, setting_value FROM settings');
        self::$cache = [];
        while ($row = $stmt->fetch()) {
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
    }
}
