<?php

class AdminController
{
    private $db;
    private $baseFolder;

    public function __construct($baseFolder)
    {
        $this->baseFolder = $baseFolder;
        $this->db = (new Database())->getConnection();
    }

    public function users()
    {
        $this->requireAdmin();
        $message = $this->handleUserAction();
        $users = $this->db->query('SELECT user_id, username, full_name, role FROM users ORDER BY full_name, username')->fetchAll();
        $pageTitle = 'User Management';
        require __DIR__ . '/../views/pages/users.php';
    }

    public function auditLogs()
    {
        $this->requireAdmin();
        $search = trim($_GET['search'] ?? '');
        $perPage = 20;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $where = '1 = 1';
        $params = [];
        if ($search !== '') {
            $where .= ' AND (al.action LIKE :search_action OR al.module LIKE :search_module OR u.username LIKE :search_user OR u.full_name LIKE :search_name)';
            $params = [
                'search_action' => '%' . $search . '%',
                'search_module' => '%' . $search . '%',
                'search_user' => '%' . $search . '%',
                'search_name' => '%' . $search . '%'
            ];
        }
        $count = $this->db->prepare("SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON u.user_id = al.user_id WHERE $where");
        $count->execute($params);
        $pages = max(1, (int) ceil((int) $count->fetchColumn() / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;
        $statement = $this->db->prepare(
            "SELECT al.log_id, al.action, al.module, al.record_id, al.old_value, al.new_value,
                    al.ip_address, al.created_at, u.username, u.full_name
             FROM audit_logs al LEFT JOIN users u ON u.user_id = al.user_id
             WHERE $where ORDER BY al.created_at DESC, al.log_id DESC
             LIMIT $perPage OFFSET $offset"
        );
        $statement->execute($params);
        $logs = $statement->fetchAll();
        $pageTitle = 'Audit Logs';
        require __DIR__ . '/../views/pages/audit-logs.php';
    }

    private function handleUserAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return null;
        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['user_id'] ?? 0);
        try {
            if ($action === 'delete_user') {
                if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
                    return ['type' => 'error', 'text' => 'You cannot delete your own account.'];
                }
                $old = $this->findUser($id);
                if (!$old) return ['type' => 'error', 'text' => 'User not found.'];
                $this->db->prepare('DELETE FROM users WHERE user_id = :id')->execute(['id' => $id]);
                $this->audit('deleted', 'users', $id, $old, null);
                return ['type' => 'success', 'text' => 'User deleted successfully.'];
            }
            if (!in_array($action, ['create_user', 'edit_user'], true)) return null;
            $username = trim($_POST['username'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'cashier';
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if ($username === '' || $fullName === '' || !in_array($role, ['cashier', 'officer', 'admin'], true)) {
                return ['type' => 'error', 'text' => 'Enter a username, full name, and valid role.'];
            }
            if ($action === 'create_user' && $password === '') return ['type' => 'error', 'text' => 'A password is required.'];
            if ($password !== '' && $password !== $confirm) return ['type' => 'error', 'text' => 'Password and confirm password do not match.'];
            if ($action === 'create_user') {
                $statement = $this->db->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (:username, :password_hash, :full_name, :role)');
                $statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'full_name' => $fullName, 'role' => $role]);
                $id = (int) $this->db->lastInsertId();
                $this->audit('created', 'users', $id, null, ['username' => $username, 'full_name' => $fullName, 'role' => $role]);
                return ['type' => 'success', 'text' => 'User created successfully.'];
            }
            $old = $this->findUser($id);
            if (!$old) return ['type' => 'error', 'text' => 'User not found.'];
            $sql = 'UPDATE users SET username = :username, full_name = :full_name, role = :role';
            $values = ['username' => $username, 'full_name' => $fullName, 'role' => $role, 'id' => $id];
            if ($password !== '') {
                $sql .= ', password_hash = :password_hash';
                $values['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->db->prepare($sql . ' WHERE user_id = :id')->execute($values);
            $this->audit('updated', 'users', $id, $old, ['username' => $username, 'full_name' => $fullName, 'role' => $role]);
            return ['type' => 'success', 'text' => 'User updated successfully.'];
        } catch (PDOException $exception) {
            return ['type' => 'error', 'text' => 'That username is already in use.'];
        }
    }

    private function findUser($id)
    {
        $statement = $this->db->prepare('SELECT user_id, username, full_name, role FROM users WHERE user_id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }

    private function audit($action, $module, $recordId, $oldValue, $newValue)
    {
        $statement = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, action, module, record_id, old_value, new_value, ip_address)
             VALUES (:user_id, :action, :module, :record_id, :old_value, :new_value, :ip_address)'
        );
        $statement->execute([
            'user_id' => $_SESSION['user_id'] ?? null, 'action' => $action, 'module' => $module,
            'record_id' => $recordId, 'old_value' => $oldValue === null ? null : json_encode($oldValue),
            'new_value' => $newValue === null ? null : json_encode($newValue),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    private function requireAdmin()
    {
        if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo '403 - Admin access required';
            exit();
        }
    }
}
