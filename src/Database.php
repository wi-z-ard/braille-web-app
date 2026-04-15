<?php
declare(strict_types=1);

class Database {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::migrate(self::$instance);
        }
        return self::$instance;
    }

    private static function migrate(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email     VARCHAR(255) UNIQUE NOT NULL,
                password  VARCHAR(255) NOT NULL,
                name      VARCHAR(255) NOT NULL,
                is_admin  TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_admin (is_admin)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS jobs (
                id          VARCHAR(64) PRIMARY KEY,
                user_id     INT UNSIGNED NOT NULL,
                filename    VARCHAR(255) NOT NULL,
                orig_name   VARCHAR(255) NOT NULL,
                status      ENUM('pending','processing','done','error') DEFAULT 'pending',
                language    VARCHAR(10) DEFAULT 'auto',
                file_type   VARCHAR(10),
                result_path TEXT,
                error       TEXT,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_created (user_id, created_at),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS rate_limits (
                ip         VARCHAR(45) NOT NULL,
                action     VARCHAR(50) NOT NULL,
                hits       INT UNSIGNED DEFAULT 1,
                window_start INT UNSIGNED NOT NULL,
                PRIMARY KEY (ip, action),
                INDEX idx_window (window_start)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS activity_logs (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id    INT UNSIGNED,
                action     VARCHAR(100) NOT NULL,
                details    TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_created (created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
}
