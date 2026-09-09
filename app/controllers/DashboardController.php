<?php

class DashboardController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index()
    {
        $role = strtolower($_SESSION['role'] ?? 'cashier');
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $isAdmin = $role === 'admin';

        $from = $isAdmin ? $this->validDate($_GET['from'] ?? '') : date('Y-m-d');
        $to = $isAdmin ? $this->validDate($_GET['to'] ?? '') : date('Y-m-d');
        $batchId = $isAdmin ? (int) ($_GET['batch_id'] ?? 0) : 0;

        if (!$from) {
            $from = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$to) {
            $to = date('Y-m-d');
        }
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $salesWhere = ['t.is_void = 0', 't.transaction_date >= :from', 't.transaction_date < DATE_ADD(:to, INTERVAL 1 DAY)'];
        $salesParams = ['from' => $from, 'to' => $to];

        if (!$isAdmin) {
            $salesWhere[] = 't.user_id = :user_id';
            $salesParams['user_id'] = $userId;
        }
        if ($batchId > 0) {
            $salesWhere[] = 'EXISTS (SELECT 1 FROM transaction_items ti_filter WHERE ti_filter.transaction_id = t.transaction_id AND ti_filter.batch_id = :batch_id)';
            $salesParams['batch_id'] = $batchId;
        }

        $salesStatement = $this->db->prepare(
            'SELECT COALESCE(SUM(t.total_amount), 0) FROM transactions t WHERE ' . implode(' AND ', $salesWhere)
        );
        $salesStatement->execute($salesParams);

        $quantityWhere = [
            't.is_void = 0',
            't.transaction_date >= :quantity_from',
            't.transaction_date < DATE_ADD(:quantity_to, INTERVAL 1 DAY)'
        ];
        $quantityParams = ['quantity_from' => $from, 'quantity_to' => $to];
        if (!$isAdmin) {
            $quantityWhere[] = 't.user_id = :quantity_user_id';
            $quantityParams['quantity_user_id'] = $userId;
        }
        if ($batchId > 0) {
            $quantityWhere[] = 'ti.batch_id = :quantity_batch_id';
            $quantityParams['quantity_batch_id'] = $batchId;
        }

        $quantityStatement = $this->db->prepare(
            'SELECT COALESCE(SUM(ti.quantity), 0)
             FROM transaction_items ti
             INNER JOIN transactions t ON t.transaction_id = ti.transaction_id
             WHERE ' . implode(' AND ', $quantityWhere)
        );
        $quantityStatement->execute($quantityParams);

        $stockStatement = $this->db->query(
            'SELECT
                COALESCE((SELECT SUM(stocks) FROM products WHERE is_batch_tracked = 0), 0)
                + COALESCE((SELECT SUM(current_quantity) FROM product_batches), 0)'
        );

        $batchStatement = $this->db->query(
            'SELECT batch_id, batch_name, delivery_date, is_active
             FROM batches ORDER BY is_active DESC, delivery_date DESC, batch_name ASC'
        );

        require __DIR__ . '/../views/pages/dashboard.php';
    }

    private function validDate($date)
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date ? $date : null;
    }
}