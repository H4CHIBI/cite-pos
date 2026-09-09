<?php

class ReleasingController
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
        $status = ($_GET['status'] ?? 'For Releasing') === 'Released' ? 'Released' : 'For Releasing';
        $message = $this->handleStatusUpdate($status);
        $search = trim($_GET['search'] ?? '');
        $batchId = isset($_GET['batch_id']) && $_GET['batch_id'] !== '' ? (int) $_GET['batch_id'] : null;
        if ($batchId === null) {
            $batchId = (int) ($this->db->query('SELECT batch_id FROM batches WHERE is_active = 1 LIMIT 1')->fetchColumn() ?: 0);
        }
        $batches = $this->db->query(
            'SELECT batch_id, batch_name, is_active FROM batches ORDER BY is_active DESC, batch_name'
        )->fetchAll();
        [$where, $params] = $this->filters($search, $batchId, $status);
        $perPage = 15;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $count = $this->db->prepare(
            "SELECT COUNT(*) FROM transaction_items ti
             INNER JOIN transactions t ON t.transaction_id = ti.transaction_id
             INNER JOIN students s ON s.student_id = ti.owner_id
             INNER JOIN departments d ON d.department_id = s.department_id
             INNER JOIN products p ON p.product_id = ti.product_id
             WHERE $where"
        );
        $count->execute($params);
        $pages = max(1, (int) ceil((int) $count->fetchColumn() / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;
        $statement = $this->db->prepare(
            "SELECT ti.item_id, ti.quantity, ti.release_status, ti.transaction_id,
                    p.name AS product_name, s.first_name, s.last_name,
                    s.grade_section, d.department_code, d.department_name,
                    b.batch_name, t.transaction_date
             FROM transaction_items ti
             INNER JOIN transactions t ON t.transaction_id = ti.transaction_id
             INNER JOIN students s ON s.student_id = ti.owner_id
             INNER JOIN departments d ON d.department_id = s.department_id
             INNER JOIN products p ON p.product_id = ti.product_id
             LEFT JOIN batches b ON b.batch_id = ti.batch_id
             WHERE $where
             ORDER BY t.transaction_date DESC, ti.item_id DESC
             LIMIT $perPage OFFSET $offset"
        );
        $statement->execute($params);
        $items = $statement->fetchAll();
        $pageTitle = 'Releasing';
        $pageStatus = $status;
        require __DIR__ . '/../views/pages/releasing.php';
    }

    public function released()
    {
        $_GET['status'] = 'Released';
        $this->index();
    }

    public function printList()
    {
        $search = trim($_GET['search'] ?? '');
        $batchId = isset($_GET['batch_id']) && $_GET['batch_id'] !== '' ? (int) $_GET['batch_id'] : null;
        if ($batchId === null) {
            $batchId = (int) ($this->db->query('SELECT batch_id FROM batches WHERE is_active = 1 LIMIT 1')->fetchColumn() ?: 0);
        }
        $status = $_GET['status'] ?? 'For Releasing';
        [$where, $params] = $this->filters($search, $batchId, $status);
        $statement = $this->db->prepare(
            "SELECT ti.quantity, p.name AS product_name, s.first_name, s.last_name,
                    s.grade_section, d.department_code, d.department_name, b.batch_name
             FROM transaction_items ti
             INNER JOIN transactions t ON t.transaction_id = ti.transaction_id
             INNER JOIN students s ON s.student_id = ti.owner_id
             INNER JOIN departments d ON d.department_id = s.department_id
             INNER JOIN products p ON p.product_id = ti.product_id
             LEFT JOIN batches b ON b.batch_id = ti.batch_id
             WHERE $where
             ORDER BY s.last_name, s.first_name, p.name"
        );
        $statement->execute($params);
        $items = $statement->fetchAll();
        $pageTitle = $status === 'Released' ? 'Released List' : 'Releasing List';
        require __DIR__ . '/../views/pages/releasing-print.php';
    }

    private function filters($search, $batchId, $status)
    {
        $where = 'ti.release_status = :status AND t.is_void = 0';
        $params = ['status' => $status];
        if ($batchId > 0) {
            $where .= ' AND ti.batch_id = :batch_id';
            $params['batch_id'] = $batchId;
        }
        if ($search !== '') {
            $where .= ' AND (p.name LIKE :search_product OR s.first_name LIKE :search_first OR s.last_name LIKE :search_last OR d.department_code LIKE :search_department)';
            $params['search_product'] = '%' . $search . '%';
            $params['search_first'] = '%' . $search . '%';
            $params['search_last'] = '%' . $search . '%';
            $params['search_department'] = '%' . $search . '%';
        }
        return [$where, $params];
    }

    private function handleStatusUpdate($status)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'update_release_status') {
            return;
        }
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $newStatus = $status === 'Released' ? 'For Releasing' : 'Released';
        try {
            $this->db->beginTransaction();
            $itemStatement = $this->db->prepare(
                'SELECT ti.*, p.name AS product_name, s.first_name, s.last_name
                 FROM transaction_items ti
                 INNER JOIN products p ON p.product_id = ti.product_id
                 INNER JOIN students s ON s.student_id = ti.owner_id
                 WHERE ti.item_id = :item_id AND ti.release_status = :old_status
                 FOR UPDATE'
            );
            $itemStatement->execute(['item_id' => $itemId, 'old_status' => $status]);
            $item = $itemStatement->fetch();
            if (!$item) {
                throw new RuntimeException('The release item is no longer available in this status.');
            }
            $quantity = (int) $item['quantity'];
            if ($item['batch_id']) {
                $stock = $this->db->prepare('SELECT current_quantity FROM product_batches WHERE batch_id = :batch_id AND product_id = :product_id FOR UPDATE');
                $stock->execute(['batch_id' => $item['batch_id'], 'product_id' => $item['product_id']]);
                $available = $stock->fetchColumn();
                if ($available === false) {
                    throw new RuntimeException('The assigned batch inventory no longer exists.');
                }
                $delta = $newStatus === 'Released' ? -$quantity : $quantity;
                if ($delta < 0 && (int) $available < $quantity) {
                    throw new RuntimeException('There is not enough inventory to release this item.');
                }
                $update = $this->db->prepare('UPDATE product_batches SET current_quantity = current_quantity + :delta WHERE batch_id = :batch_id AND product_id = :product_id');
                $update->execute(['delta' => $delta, 'batch_id' => $item['batch_id'], 'product_id' => $item['product_id']]);
            } else {
                $delta = $newStatus === 'Released' ? -$quantity : $quantity;
                if ($delta < 0) {
                    $stock = $this->db->prepare('SELECT stocks FROM products WHERE product_id = :product_id FOR UPDATE');
                    $stock->execute(['product_id' => $item['product_id']]);
                    if ((int) $stock->fetchColumn() < $quantity) {
                        throw new RuntimeException('There is not enough inventory to release this item.');
                    }
                }
                $update = $this->db->prepare('UPDATE products SET stocks = stocks + :delta WHERE product_id = :product_id');
                $update->execute(['delta' => $delta, 'product_id' => $item['product_id']]);
            }
            $this->db->prepare('UPDATE transaction_items SET release_status = :new_status WHERE item_id = :item_id')->execute(['new_status' => $newStatus, 'item_id' => $itemId]);
            $this->audit('updated release status', 'transaction_items', $itemId, ['release_status' => $status], ['release_status' => $newStatus, 'product' => $item['product_name'], 'owner' => trim($item['first_name'] . ' ' . $item['last_name'])]);
            $this->db->commit();
            return ['type' => 'success', 'text' => 'Release status updated successfully.'];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['type' => 'error', 'text' => $exception->getMessage()];
        }
    }

    private function audit($action, $module, $recordId, $oldValue, $newValue)
    {
        $statement = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, action, module, record_id, old_value, new_value, ip_address)
             VALUES (:user_id, :action, :module, :record_id, :old_value, :new_value, :ip_address)'
        );
        $statement->execute([
            'user_id' => $_SESSION['user_id'] ?? null, 'action' => $action, 'module' => $module,
            'record_id' => $recordId, 'old_value' => json_encode($oldValue),
            'new_value' => json_encode($newValue), 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
