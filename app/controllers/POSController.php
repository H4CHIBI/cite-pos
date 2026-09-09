<?php

class POSController
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
        $receipt = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $receipt] = $this->completeSale();
        }
        $products = $this->db->query(
            'SELECT product_id, name, description, base_price, image_path, is_batch_tracked, requires_releasing, stocks
             FROM products ORDER BY name'
        )->fetchAll();
        $activeBatch = $this->db->query(
            'SELECT batch_id, batch_name FROM batches WHERE is_active = 1 LIMIT 1'
        )->fetch();
        $departments = $this->db->query(
            'SELECT department_id, department_code, department_name FROM departments ORDER BY department_name'
        )->fetchAll();
        if (!$departments) {
            $this->db->exec("INSERT INTO departments (department_code, department_name) VALUES ('CITE', 'College of Information Technology Education')");
            $departments = $this->db->query(
                'SELECT department_id, department_code, department_name FROM departments ORDER BY department_name'
            )->fetchAll();
        }
        require __DIR__ . '/../views/pages/pos.php';
    }

    private function completeSale()
    {
        $representative = trim($_POST['representative_name'] ?? '');
        $position = trim($_POST['representative_position'] ?? 'Student');
        $amountPaid = filter_var($_POST['amount_paid'] ?? null, FILTER_VALIDATE_FLOAT);
        $productIds = $_POST['product_id'] ?? [];
        $ownerNames = $_POST['owner_name'] ?? [];
        $departmentIds = $_POST['department_id'] ?? [];
        $years = $_POST['year'] ?? [];
        $sections = $_POST['section'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        if ($representative === '' || !is_array($productIds) || count($productIds) === 0) {
            return [['type' => 'error', 'text' => 'Add at least one product and enter the ordering person name.'], null];
        }
        if ($amountPaid === false || $amountPaid < 0) {
            return [['type' => 'error', 'text' => 'Enter a valid amount paid.'], null];
        }

        try {
            $this->db->beginTransaction();
            $items = [];
            $total = 0;
            $batchId = null;
            foreach ($productIds as $index => $rawProductId) {
                $productId = (int) $rawProductId;
                $ownerName = trim($ownerNames[$index] ?? '');
                $departmentId = (int) ($departmentIds[$index] ?? 0);
                $year = trim($years[$index] ?? '');
                $section = trim($sections[$index] ?? '');
                $quantity = filter_var($quantities[$index] ?? null, FILTER_VALIDATE_INT);
                if ($productId < 1 || $ownerName === '' || $departmentId < 1 || $year === '' || $quantity === false || $quantity < 1) {
                    throw new RuntimeException('Each product must have an owner, department, year, and valid quantity.');
                }
                $productStatement = $this->db->prepare('SELECT * FROM products WHERE product_id = :id FOR UPDATE');
                $productStatement->execute(['id' => $productId]);
                $product = $productStatement->fetch();
                if (!$product) {
                    throw new RuntimeException('A selected product no longer exists.');
                }
                $lineBatchId = null;
                if ((int) $product['is_batch_tracked'] === 1) {
                    $batch = $this->db->query('SELECT batch_id FROM batches WHERE is_active = 1 LIMIT 1')->fetch();
                    if (!$batch) throw new RuntimeException('No active batch is available for a batch-tracked product.');
                    $lineBatchId = (int) $batch['batch_id'];
                    $batchStock = $this->db->prepare('SELECT current_quantity FROM product_batches WHERE batch_id = :batch_id AND product_id = :product_id FOR UPDATE');
                    $batchStock->execute(['batch_id' => $lineBatchId, 'product_id' => $productId]);
                    $stock = $batchStock->fetch();
                    if (!$stock) throw new RuntimeException('The selected product is not assigned to the active batch.');
                    if (!(int) $product['requires_releasing']) {
                        $pending = $this->db->prepare("SELECT COALESCE(SUM(ti.quantity), 0) FROM transaction_items ti INNER JOIN transactions t ON t.transaction_id = ti.transaction_id WHERE ti.batch_id = :batch_id AND ti.product_id = :product_id AND ti.release_status = 'For Releasing' AND t.is_void = 0");
                        $pending->execute(['batch_id' => $lineBatchId, 'product_id' => $productId]);
                        if (((int) $stock['current_quantity'] - (int) $pending->fetchColumn()) < $quantity) throw new RuntimeException('Insufficient batch stock for ' . $product['name'] . '.');
                    }
                } else {
                    if (!(int) $product['requires_releasing']) {
                        $pending = $this->db->prepare("SELECT COALESCE(SUM(ti.quantity), 0) FROM transaction_items ti INNER JOIN transactions t ON t.transaction_id = ti.transaction_id WHERE ti.product_id = :product_id AND ti.release_status = 'For Releasing' AND t.is_void = 0");
                        $pending->execute(['product_id' => $productId]);
                        if ((int) $product['stocks'] - (int) $pending->fetchColumn() < $quantity) throw new RuntimeException('Insufficient stock for ' . $product['name'] . '.');
                    }
                }
                $ownerId = $this->findOrCreateOwner($ownerName, $departmentId, $year, $section);
                $lineTotal = (float) $product['base_price'] * $quantity;
                $total += $lineTotal;
                $items[] = ['product_id' => $productId, 'batch_id' => $lineBatchId, 'owner_id' => $ownerId, 'quantity' => $quantity, 'name' => $product['name'], 'price' => (float) $product['base_price'], 'owner' => $ownerName, 'release_status' => 'For Releasing'];
                $batchId = $lineBatchId ?: $batchId;
            }
            if ($amountPaid < $total) throw new RuntimeException('Amount paid is less than the total sale.');
            $transaction = $this->db->prepare(
                'INSERT INTO transactions (user_id, representative_name, representative_position, total_amount, payment_method, amount_paid, change_amount)
                 VALUES (:user_id, :representative, :position, :total, :payment, :paid, :change)'
            );
            $transaction->execute(['user_id' => $_SESSION['user_id'], 'representative' => $representative, 'position' => $position, 'total' => $total, 'payment' => 'Cash', 'paid' => $amountPaid, 'change' => $amountPaid - $total]);
            $transactionId = (int) $this->db->lastInsertId();
            $itemStatement = $this->db->prepare(
                'INSERT INTO transaction_items (transaction_id, product_id, batch_id, owner_id, quantity, price_at_purchase, release_status)
                 VALUES (:transaction_id, :product_id, :batch_id, :owner_id, :quantity, :price, :status)'
            );
            foreach ($items as $item) $itemStatement->execute(['transaction_id' => $transactionId, 'product_id' => $item['product_id'], 'batch_id' => $item['batch_id'], 'owner_id' => $item['owner_id'], 'quantity' => $item['quantity'], 'price' => $item['price'], 'status' => $item['release_status']]);
            $this->audit($transactionId, $total, $representative, $items);
            $this->db->commit();
            return [null, ['id' => $transactionId, 'items' => $items, 'representative' => $representative, 'position' => $position, 'total' => $total, 'paid' => $amountPaid, 'change' => $amountPaid - $total, 'date' => date('F j, Y g:i A')]];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return [['type' => 'error', 'text' => $exception->getMessage()], null];
        }
    }

    private function findOrCreateOwner($fullName, $departmentId, $year, $section)
    {
        $parts = preg_split('/\s+/', $fullName, 2);
        $first = $parts[0];
        $last = $parts[1] ?? '-';
        $gradeSection = $year . ($section === '' ? '' : ' - ' . $section);
        $lookup = $this->db->prepare('SELECT student_id FROM students WHERE first_name = :first AND last_name = :last AND department_id = :department AND grade_section = :grade_section LIMIT 1');
        $lookup->execute(['first' => $first, 'last' => $last, 'department' => $departmentId, 'grade_section' => $gradeSection]);
        $existing = $lookup->fetchColumn();
        if ($existing) return (int) $existing;
        $department = $this->db->prepare('SELECT department_id FROM departments WHERE department_id = :id');
        $department->execute(['id' => $departmentId]);
        if (!$department->fetchColumn()) {
            throw new RuntimeException('The selected department does not exist.');
        }
        if (!$departmentId) {
            $this->db->exec("INSERT INTO departments (department_code, department_name) VALUES ('CITE', 'College of Information Technology Education')");
            $departmentId = $this->db->lastInsertId();
        }
        $studentNumber = 'POS-' . strtoupper(substr(hash('sha256', $fullName . microtime(true)), 0, 16));
        $insert = $this->db->prepare('INSERT INTO students (student_number, first_name, last_name, grade_section, department_id) VALUES (:number, :first, :last, :section, :department)');
        $insert->execute(['number' => $studentNumber, 'first' => $first, 'last' => $last, 'section' => $gradeSection, 'department' => $departmentId]);
        return (int) $this->db->lastInsertId();
    }

    private function audit($id, $total, $representative, $items)
    {
        $statement = $this->db->prepare('INSERT INTO audit_logs (user_id, action, module, record_id, new_value, ip_address) VALUES (:user_id, :action, :module, :record_id, :new_value, :ip)');
        $statement->execute(['user_id' => $_SESSION['user_id'], 'action' => 'created sale', 'module' => 'transactions', 'record_id' => $id, 'new_value' => json_encode(['total' => $total, 'representative' => $representative, 'items' => $items]), 'ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
