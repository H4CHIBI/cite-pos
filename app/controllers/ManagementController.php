<?php

class ManagementController
{
    private $db;
    private $baseFolder;

    public function __construct($baseFolder)
    {
        $this->baseFolder = $baseFolder;
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function products()
    {
        $this->requireAdmin();
        $message = $this->handleProductCreate();
        $products = $this->db->query('SELECT * FROM products ORDER BY product_id DESC')->fetchAll();
        $this->render('products', ['products' => $products, 'message' => $message]);
    }

    public function batches()
    {
        $this->requireAdmin();
        $message = $this->handleBatchActions();
        $batches = $this->db->query(
            'SELECT batch_id, batch_name, delivery_date, is_active FROM batches ORDER BY is_active DESC, delivery_date DESC, batch_name ASC'
        )->fetchAll();
        $this->render('batches', compact('batches', 'message'));
    }

    public function inventory()
    {
        $this->requireAdmin();
        $message = $this->handleInventoryUpdate();
        $batches = $this->db->query(
            'SELECT batch_id, batch_name, is_active FROM batches ORDER BY is_active DESC, batch_name ASC'
        )->fetchAll();
        $trackedProducts = $this->db->query(
            'SELECT product_id, name FROM products WHERE is_batch_tracked = 1 ORDER BY name'
        )->fetchAll();
        $assignmentProducts = $this->db->query(
            'SELECT batch_id, product_id FROM product_batches'
        )->fetchAll();
        $assignments = $this->db->query(
            'SELECT pb.product_batch_id, pb.initial_quantity, pb.current_quantity, b.batch_name, p.name
             FROM product_batches pb
             INNER JOIN batches b ON b.batch_id = pb.batch_id
             INNER JOIN products p ON p.product_id = pb.product_id
             ORDER BY b.is_active DESC, b.batch_name, p.name'
        )->fetchAll();
        $this->render('inventory', compact('batches', 'trackedProducts', 'assignmentProducts', 'assignments', 'message'));
    }

    public function departments()
    {
        $this->requireAdmin();
        $message = $this->handleDepartmentActions();
        $departments = $this->db->query(
            'SELECT department_id, department_code, department_name FROM departments ORDER BY department_name'
        )->fetchAll();
        $this->render('departments', compact('departments', 'message'));
    }

    private function handleProductCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = $_POST['action'] ?? '';
        if ($action === 'delete_product') {
            return $this->deleteProduct((int) ($_POST['product_id'] ?? 0));
        }
        if ($action === 'edit_product') {
            return $this->editProduct((int) ($_POST['product_id'] ?? 0));
        }
        if ($action !== 'create_product') {
            return null;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $basePrice = filter_var($_POST['base_price'] ?? null, FILTER_VALIDATE_FLOAT);
        $isBatchTracked = isset($_POST['is_batch_tracked']) ? 1 : 0;
        $requiresReleasing = isset($_POST['requires_releasing']) ? 1 : 0;

        if ($name === '' || $basePrice === false || $basePrice < 0) {
            return ['type' => 'error', 'text' => 'Enter a product name and a valid non-negative price.'];
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if ($file['error'] !== UPLOAD_ERR_OK || !isset($allowed[$mime]) || $file['size'] > 5 * 1024 * 1024) {
                return ['type' => 'error', 'text' => 'Upload a JPG, PNG, or WEBP image up to 5 MB.'];
            }
            $directory = __DIR__ . '/../../public/assets/images/products';
            if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
                return ['type' => 'error', 'text' => 'The product image directory could not be created.'];
            }
            $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
                return ['type' => 'error', 'text' => 'The product image could not be saved.'];
            }
            $imagePath = 'assets/images/products/' . $filename;
        }

        $statement = $this->db->prepare(
            'INSERT INTO products (name, description, base_price, image_path, is_batch_tracked, requires_releasing, stocks)
             VALUES (:name, :description, :base_price, :image_path, :is_batch_tracked, :requires_releasing, 0)'
        );
        $statement->execute([
            'name' => $name,
            'description' => $description,
            'base_price' => $basePrice,
            'image_path' => $imagePath,
            'is_batch_tracked' => $isBatchTracked,
            'requires_releasing' => $requiresReleasing
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->audit('created', 'products', $id, null, ['name' => $name, 'base_price' => $basePrice]);
        return ['type' => 'success', 'text' => 'Product created successfully.'];
    }

    private function handleDepartmentActions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        $action = $_POST['action'] ?? '';
        if ($action === 'delete_department') {
            $id = (int) ($_POST['department_id'] ?? 0);
            $department = $this->find('departments', 'department_id', $id);
            try {
                $this->db->prepare('DELETE FROM departments WHERE department_id = :id')->execute(['id' => $id]);
                $this->audit('deleted', 'departments', $id, $department, null);
                return ['type' => 'success', 'text' => 'Department deleted successfully.'];
            } catch (PDOException $exception) {
                return ['type' => 'error', 'text' => 'This department cannot be deleted because it is assigned to students.'];
            }
        }
        if ($action === 'edit_department') {
            $id = (int) ($_POST['department_id'] ?? 0);
            $code = strtoupper(trim($_POST['department_code'] ?? ''));
            $name = trim($_POST['department_name'] ?? '');
            if ($id < 1 || $code === '' || $name === '') {
                return ['type' => 'error', 'text' => 'Enter a department code and name.'];
            }
            try {
                $oldDepartment = $this->find('departments', 'department_id', $id);
                $this->db->prepare(
                    'UPDATE departments SET department_code = :code, department_name = :name WHERE department_id = :id'
                )->execute(['code' => $code, 'name' => $name, 'id' => $id]);
                $this->audit('updated', 'departments', $id, $oldDepartment, ['department_code' => $code, 'department_name' => $name]);
                return ['type' => 'success', 'text' => 'Department updated successfully.'];
            } catch (PDOException $exception) {
                return ['type' => 'error', 'text' => 'That department code already exists.'];
            }
        }
        if ($action !== 'create_department') {
            return null;
        }
        $code = strtoupper(trim($_POST['department_code'] ?? ''));
        $name = trim($_POST['department_name'] ?? '');
        if ($code === '' || $name === '') {
            return ['type' => 'error', 'text' => 'Enter a department code and name.'];
        }
        try {
            $this->db->prepare(
                'INSERT INTO departments (department_code, department_name) VALUES (:code, :name)'
            )->execute(['code' => $code, 'name' => $name]);
            $this->audit('created', 'departments', (int) $this->db->lastInsertId(), null, ['department_code' => $code, 'department_name' => $name]);
            return ['type' => 'success', 'text' => 'Department created successfully.'];
        } catch (PDOException $exception) {
            return ['type' => 'error', 'text' => 'That department code already exists.'];
        }
    }

    private function editProduct($id)
    {
        $product = $this->find('products', 'product_id', $id);
        if (!$product) return ['type' => 'error', 'text' => 'Product not found.'];
        $name = trim($_POST['name'] ?? '');
        $basePrice = filter_var($_POST['base_price'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($name === '' || $basePrice === false || $basePrice < 0) return ['type' => 'error', 'text' => 'Enter a valid product name and price.'];
        $sql = 'UPDATE products SET name = :name, base_price = :base_price, is_batch_tracked = :tracked WHERE product_id = :id';
        $this->db->prepare($sql)->execute(['name' => $name, 'base_price' => $basePrice, 'tracked' => isset($_POST['is_batch_tracked']) ? 1 : 0, 'id' => $id]);
        $this->audit('updated', 'products', $id, $product, ['name' => $name, 'base_price' => $basePrice]);
        return ['type' => 'success', 'text' => 'Product updated successfully.'];
    }

    private function deleteProduct($id)
    {
        $product = $this->find('products', 'product_id', $id);
        if (!$product) return ['type' => 'error', 'text' => 'Product not found.'];
        try {
            $this->db->prepare('DELETE FROM products WHERE product_id = :id')->execute(['id' => $id]);
        } catch (PDOException $exception) {
            return ['type' => 'error', 'text' => 'This product cannot be deleted because it is used in inventory or transactions.'];
        }
        $this->audit('deleted', 'products', $id, $product, null);
        return ['type' => 'success', 'text' => 'Product deleted successfully.'];
    }

    private function handleBatchActions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        $action = $_POST['action'] ?? '';
        if ($action === 'delete_batch') {
            return $this->deleteBatch((int) ($_POST['batch_id'] ?? 0));
        }
        if ($action === 'edit_batch') {
            return $this->editBatch((int) ($_POST['batch_id'] ?? 0));
        }
        if ($action === 'create_batch') {
            $name = trim($_POST['batch_name'] ?? '');
            $deliveryDate = $_POST['delivery_date'] ?: null;
            if ($name === '') {
                return ['type' => 'error', 'text' => 'Enter a batch name.'];
            }
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            if ($isActive) {
                $this->db->exec('UPDATE batches SET is_active = 0');
            }
            $statement = $this->db->prepare('INSERT INTO batches (batch_name, delivery_date, is_active) VALUES (:name, :delivery_date, :is_active)');
            $statement->execute(['name' => $name, 'delivery_date' => $deliveryDate, 'is_active' => $isActive]);
            $id = (int) $this->db->lastInsertId();
            $this->audit('created', 'batches', $id, null, ['batch_name' => $name]);
            return ['type' => 'success', 'text' => 'Batch created successfully.'];
        }
        if ($action === 'assign_product') {
            return $this->assignProduct();
        }
        return null;
    }

    private function handleInventoryUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        $action = $_POST['action'] ?? '';
        if ($action === 'assign_product') {
            return $this->assignProduct();
        }
        if ($action === 'edit_assignment' || $action === 'delete_assignment') {
            return $this->changeAssignment($action);
        }
        if ($action === 'edit_stock') {
            return $this->editStock((int) ($_POST['product_id'] ?? 0));
        }
        if ($action === 'delete_stock') {
            return $this->editStock((int) ($_POST['product_id'] ?? 0), true);
        }
        return null;
    }

    private function assignProduct()
    {
        $batchId = (int) ($_POST['batch_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $rawQuantity = trim((string) ($_POST['quantity'] ?? ''));
        $quantity = $rawQuantity === '' ? 0 : filter_var($rawQuantity, FILTER_VALIDATE_INT);
        if ($batchId < 1 || $productId < 1 || $quantity === false || $quantity < 0) {
            return ['type' => 'error', 'text' => 'Select a batch, a tracked product, and enter a valid quantity.'];
        }
        $check = $this->db->prepare('SELECT name FROM products WHERE product_id = :product_id AND is_batch_tracked = 1');
        $check->execute(['product_id' => $productId]);
        if (!$check->fetch()) {
            return ['type' => 'error', 'text' => 'Only batch-tracked products can be assigned to a batch.'];
        }
        $duplicate = $this->db->prepare(
            'SELECT 1 FROM product_batches WHERE batch_id = :batch_id AND product_id = :product_id'
        );
        $duplicate->execute(['batch_id' => $batchId, 'product_id' => $productId]);
        if ($duplicate->fetchColumn()) {
            return ['type' => 'error', 'text' => 'This product is already assigned to the selected batch.'];
        }
        $statement = $this->db->prepare(
            'INSERT INTO product_batches (batch_id, product_id, initial_quantity, current_quantity)
             VALUES (:batch_id, :product_id, :quantity, :quantity)'
        );
        $statement->execute(['batch_id' => $batchId, 'product_id' => $productId, 'quantity' => $quantity]);
        $this->audit('assigned product to batch', 'product_batches', (int) $this->db->lastInsertId(), null, ['batch_id' => $batchId, 'product_id' => $productId, 'quantity' => $quantity]);
        return ['type' => 'success', 'text' => 'Product assigned to batch successfully.'];
    }

    private function editStock($productId, $delete = false)
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE product_id = :id AND is_batch_tracked = 0');
        $statement->execute(['id' => $productId]);
        $product = $statement->fetch();
        if (!$product) {
            return ['type' => 'error', 'text' => 'Standard inventory product not found.'];
        }
        $quantity = $delete ? 0 : filter_var($_POST['stocks'] ?? null, FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) {
            return ['type' => 'error', 'text' => 'Enter a valid stock quantity.'];
        }
        $update = $this->db->prepare('UPDATE products SET stocks = :stocks WHERE product_id = :id');
        $update->execute(['stocks' => $quantity, 'id' => $productId]);
        $this->audit($delete ? 'deleted inventory stock' : 'updated inventory stock', 'products', $productId, ['stocks' => (int) $product['stocks']], ['stocks' => $quantity]);
        return ['type' => 'success', 'text' => $delete ? 'Inventory stock deleted.' : 'Inventory stock updated.'];
    }

    private function deleteBatch($id)
    {
        $batch = $this->find('batches', 'batch_id', $id);
        if (!$batch) return ['type' => 'error', 'text' => 'Batch not found.'];
        try {
            $statement = $this->db->prepare('DELETE FROM batches WHERE batch_id = :id');
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
            return ['type' => 'error', 'text' => 'This batch cannot be deleted because it has assigned products or transactions.'];
        }
        $this->audit('deleted', 'batches', $id, $batch, null);
        return ['type' => 'success', 'text' => 'Batch deleted successfully.'];
    }

    private function editBatch($id)
    {
        $batch = $this->find('batches', 'batch_id', $id);
        $name = trim($_POST['batch_name'] ?? '');
        if (!$batch || $name === '') return ['type' => 'error', 'text' => 'Enter a valid batch name.'];
        if (isset($_POST['is_active'])) $this->db->exec('UPDATE batches SET is_active = 0');
        $statement = $this->db->prepare('UPDATE batches SET batch_name = :name, delivery_date = :delivery_date, is_active = :active WHERE batch_id = :id');
        $statement->execute(['name' => $name, 'delivery_date' => $_POST['delivery_date'] ?: null, 'active' => isset($_POST['is_active']) ? 1 : 0, 'id' => $id]);
        $this->audit('updated', 'batches', $id, $batch, ['batch_name' => $name]);
        return ['type' => 'success', 'text' => 'Batch updated successfully.'];
    }

    private function changeAssignment($action)
    {
        $id = (int) ($_POST['product_batch_id'] ?? 0);
        $old = $this->find('product_batches', 'product_batch_id', $id);
        if (!$old) return ['type' => 'error', 'text' => 'Batch assignment not found.'];
        if ($action === 'delete_assignment') {
            $this->db->prepare('DELETE FROM product_batches WHERE product_batch_id = :id')->execute(['id' => $id]);
            $this->audit('deleted batch assignment', 'product_batches', $id, $old, null);
            return ['type' => 'success', 'text' => 'Batch assignment deleted.'];
        }
        $quantity = filter_var($_POST['current_quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) return ['type' => 'error', 'text' => 'Enter a valid current quantity.'];
        $this->db->prepare('UPDATE product_batches SET current_quantity = :quantity WHERE product_batch_id = :id')->execute(['quantity' => $quantity, 'id' => $id]);
        $this->audit('updated batch inventory', 'product_batches', $id, $old, ['current_quantity' => $quantity]);
        return ['type' => 'success', 'text' => 'Batch inventory updated.'];
    }

    private function find($table, $key, $id)
    {
        $statement = $this->db->prepare("SELECT * FROM `$table` WHERE `$key` = :id");
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
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId ?: null,
            'old_value' => $oldValue === null ? null : json_encode($oldValue),
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

    private function render($page, $data)
    {
        extract($data);
        $baseFolder = $this->baseFolder;
        $pageTitle = ucfirst($page);
        require __DIR__ . '/../views/pages/management.php';
    }
}
