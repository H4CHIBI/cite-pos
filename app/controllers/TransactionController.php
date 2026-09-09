<?php

class TransactionController
{
    private $db;
    private $baseFolder;

    public function __construct($baseFolder)
    {
        $this->baseFolder = $baseFolder;
        $this->db = (new Database())->getConnection();
    }

    public function index()
    {
        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $this->handleAction();
        }

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE CAST(t.transaction_id AS CHAR) LIKE :search
                OR t.representative_name LIKE :search_name
                OR u.full_name LIKE :search_cashier';
            $params['search'] = '%' . $search . '%';
            $params['search_name'] = '%' . $search . '%';
            $params['search_cashier'] = '%' . $search . '%';
        }

        $count = $this->db->prepare(
            "SELECT COUNT(*) FROM transactions t INNER JOIN users u ON u.user_id = t.user_id $where"
        );
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;

        $statement = $this->db->prepare(
            "SELECT t.transaction_id, t.representative_name, t.representative_position,
                    t.transaction_date, t.total_amount, t.payment_method, t.amount_paid,
                    t.is_void,
                    t.change_amount, u.full_name AS cashier,
                    COALESCE(SUM(ti.quantity), 0) AS item_quantity
             FROM transactions t
             INNER JOIN users u ON u.user_id = t.user_id
             LEFT JOIN transaction_items ti ON ti.transaction_id = t.transaction_id
             $where
             GROUP BY t.transaction_id
             ORDER BY t.transaction_date DESC, t.transaction_id DESC
             LIMIT $perPage OFFSET $offset"
        );
        $statement->execute($params);
        $transactions = $statement->fetchAll();
        $pageTitle = 'Transactions';
        require __DIR__ . '/../views/pages/transactions.php';
    }

    public function printTransaction()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $transaction = $this->findTransaction($id);
        if (!$transaction) {
            http_response_code(404);
            echo '404 - Transaction Not Found';
            return;
        }
        $pageTitle = 'Print Transaction';
        require __DIR__ . '/../views/pages/transaction-print.php';
    }

    private function handleAction()
    {
        $action = $_POST['action'] ?? '';
        if ($action !== 'void_transaction') {
            return null;
        }
        if (!$this->verifyAdminPassword($_POST['admin_password'] ?? '')) {
            return ['type' => 'error', 'text' => 'The admin password is incorrect.'];
        }
        $id = (int) ($_POST['transaction_id'] ?? 0);
        $transaction = $this->findTransaction($id);
        if (!$transaction) {
            return ['type' => 'error', 'text' => 'Transaction not found.'];
        }
        if ($action === 'void_transaction' && (int) $transaction['is_void'] === 1) {
            return ['type' => 'error', 'text' => 'This transaction is already void.'];
        }

        try {
            $this->db->beginTransaction();
            foreach ($transaction['items'] as $item) {
                if ($item['release_status'] !== 'Released') {
                    continue;
                }
                if ($item['batch_id']) {
                    $this->db->prepare(
                        'UPDATE product_batches SET current_quantity = current_quantity + :quantity WHERE batch_id = :batch_id AND product_id = :product_id'
                    )->execute(['quantity' => $item['quantity'], 'batch_id' => $item['batch_id'], 'product_id' => $item['product_id']]);
                } else {
                    $this->db->prepare(
                        'UPDATE products SET stocks = stocks + :quantity WHERE product_id = :product_id'
                    )->execute(['quantity' => $item['quantity'], 'product_id' => $item['product_id']]);
                }
            }
            $this->db->prepare('UPDATE transactions SET is_void = 1 WHERE transaction_id = :id')->execute(['id' => $id]);
            $this->db->prepare(
                "UPDATE transaction_items SET release_status = 'Void' WHERE transaction_id = :id"
            )->execute(['id' => $id]);
            $this->audit('voided', $id, $transaction, ['is_void' => 1]);
            $message = 'Transaction voided and inventory restored.';
            $this->db->commit();
            return ['type' => 'success', 'text' => $message];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['type' => 'error', 'text' => $exception->getMessage()];
        }
    }

    private function findTransaction($id)
    {
        $statement = $this->db->prepare(
            'SELECT t.*, u.full_name AS cashier FROM transactions t INNER JOIN users u ON u.user_id = t.user_id WHERE t.transaction_id = :id'
        );
        $statement->execute(['id' => $id]);
        $transaction = $statement->fetch();
        if (!$transaction) {
            return null;
        }
        $items = $this->db->prepare(
            'SELECT ti.*, p.name AS product_name, s.first_name, s.last_name
             FROM transaction_items ti
             INNER JOIN products p ON p.product_id = ti.product_id
             INNER JOIN students s ON s.student_id = ti.owner_id
             WHERE ti.transaction_id = :id ORDER BY ti.item_id'
        );
        $items->execute(['id' => $id]);
        $transaction['items'] = $items->fetchAll();
        return $transaction;
    }

    private function verifyAdminPassword($password)
    {
        if ($password === '') {
            return false;
        }
        $statement = $this->db->query("SELECT password_hash FROM users WHERE role = 'admin'");
        foreach ($statement->fetchAll() as $admin) {
            if (password_verify($password, $admin['password_hash'])) {
                return true;
            }
        }
        return false;
    }

    private function audit($action, $id, $oldValue, $newValue)
    {
        $statement = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, action, module, record_id, old_value, new_value, ip_address)
             VALUES (:user_id, :action, :module, :record_id, :old_value, :new_value, :ip)'
        );
        $statement->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'module' => 'transactions',
            'record_id' => $id,
            'old_value' => json_encode($oldValue),
            'new_value' => $newValue === null ? null : json_encode($newValue),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
