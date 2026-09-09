<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../config/Database.php';

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                return "Please enter both username and password.";
            }

            try {
                $stmt = $this->db->prepare("SELECT user_id, username, password_hash, full_name, role FROM users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['fullname'] = $user['full_name'];
                    $_SESSION['role'] = $user['role']; 
                    $this->audit('logged in', 'authentication', (int) $user['user_id'], null, ['username' => $user['username']]);

                    header("Location: dashboard");
                    exit();
                } else {
                    $this->audit('failed login', 'authentication', $user ? (int) $user['user_id'] : null, null, ['username' => $username]);
                    return "Invalid username or password.";
                }
            } catch (PDOException $e) {
                return "A system error occurred. Please try again.";
            }
        }
        return null;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->audit('logged out', 'authentication', (int) ($_SESSION['user_id'] ?? 0), null, ['username' => $_SESSION['username'] ?? null]);
        $_SESSION = [];
        session_destroy();

        header("Location: login");
        exit();
    }

    private function audit($action, $module, $recordId, $oldValue, $newValue)
    {
        $statement = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, action, module, record_id, old_value, new_value, ip_address)
             VALUES (:user_id, :action, :module, :record_id, :old_value, :new_value, :ip_address)'
        );
        $statement->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId ?: null,
            'old_value' => $oldValue === null ? null : json_encode($oldValue),
            'new_value' => $newValue === null ? null : json_encode($newValue),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}