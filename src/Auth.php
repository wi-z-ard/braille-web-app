<?php
declare(strict_types=1);

class Auth {

    public static function register(string $email, string $password, string $name): int {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        if (!$email) throw new RuntimeException('Invalid email.');
        if (strlen($password) < 8) throw new RuntimeException('Password too short.');

        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $db   = Database::get();

        try {
            $db->prepare('INSERT INTO users (email, password, name) VALUES (?,?,?)')
               ->execute([$email, $hash, Security::sanitizeString($name)]);
            $userId = (int) $db->lastInsertId();
            ActivityLog::log('register', 'New user registered', $userId);
            return $userId;
        } catch (PDOException $e) {
            throw new RuntimeException('Email already registered.');
        }
    }

    public static function login(string $email, string $password): array {
        $db   = Database::get();
        $stmt = $db->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new RuntimeException('Invalid credentials.');
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email']= $user['email'];

        ActivityLog::log('login', 'User logged in', $user['id']);
        
        return $user;
    }

    public static function logout(): void {
        session_destroy();
        header('Location: ' . BASE_PATH . '/');
        exit;
    }

    public static function current(): ?array {
        if (empty($_SESSION['user_id'])) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
        ];
    }
}
