<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// START SESSION FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load Environment Variables & Database
require_once __DIR__ . '/../app/Config/env.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../app/Config/Database.php';

// 2. Build a Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']); 
$baseFolder = str_replace('\\', '/', dirname($scriptName)); 
$route = str_replace($baseFolder, '', $requestUri);
$route = rtrim($route, '/');

// Helper variables for routing security
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = strtolower($_SESSION['role'] ?? '');

// Ensure logged out users can only access the login page
if (!$isLoggedIn && !in_array($route, ['', '/', '/login'])) {
    header("Location: " . $baseFolder . "/login");
    exit();
}

// Ensure logged in users cannot access the login page
if ($isLoggedIn && in_array($route, ['', '/', '/login'])) {
    header("Location: " . $baseFolder . "/dashboard");
    exit();
}

// 3. Route the Request
switch ($route) {
    case '':
    case '/':
    case '/login':
        $errorMessage = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $auth = new AuthController();
            $errorMessage = $auth->login(); 
        }
        require_once __DIR__ . '/../app/Views/pages/login.php';
        break;

    case '/logout':
    case 'logout':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->logout();
        break;

    // --- ACCESSIBLE TO ALL ROLES ---
    case '/dashboard':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $dashboard = new DashboardController();
        $dashboard->index();
        break;

    case '/pos':
        if ($userRole !== 'cashier') {
            http_response_code(403);
            echo "403 - Cashier access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/POSController.php';
        $pos = new POSController($baseFolder);
        $pos->index();
        break;

    case '/transactions':
        if (!in_array($userRole, ['admin', 'cashier'], true)) {
            http_response_code(403);
            echo "403 - Admin or cashier access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/TransactionController.php';
        $transactions = new TransactionController($baseFolder);
        $transactions->index();
        break;

    case '/transactions/print':
        if (!in_array($userRole, ['admin', 'cashier'], true)) {
            http_response_code(403);
            echo "403 - Admin or cashier access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/TransactionController.php';
        $transactions = new TransactionController($baseFolder);
        $transactions->printTransaction();
        break;

    case '/releasing':
        if (!in_array($userRole, ['admin', 'officer'], true)) {
            http_response_code(403);
            echo "403 - Admin or officer access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/ReleasingController.php';
        $releasing = new ReleasingController($baseFolder);
        $releasing->index();
        break;

    case '/releasing/print':
        if (!in_array($userRole, ['admin', 'officer'], true)) {
            http_response_code(403);
            echo "403 - Admin or officer access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/ReleasingController.php';
        $releasing = new ReleasingController($baseFolder);
        $releasing->printList();
        break;

    case '/released':
        if (!in_array($userRole, ['admin', 'officer'], true)) {
            http_response_code(403);
            echo "403 - Admin or officer access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/ReleasingController.php';
        $releasing = new ReleasingController($baseFolder);
        $releasing->released();
        break;

    case '/released/print':
        if (!in_array($userRole, ['admin', 'officer'], true)) {
            http_response_code(403);
            echo "403 - Admin or officer access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/ReleasingController.php';
        $releasing = new ReleasingController($baseFolder);
        $_GET['status'] = 'Released';
        $releasing->printList();
        break;

    case '/products':
    case '/batches':
    case '/inventory':
    case '/departments':
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo "403 - Admin access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/ManagementController.php';
        $management = new ManagementController($baseFolder);
        $management->{trim($route, '/') }();
        break;

    case '/audit-logs':
    case '/users':
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo "403 - Admin access required";
            break;
        }
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $admin = new AdminController($baseFolder);
        if ($route === '/audit-logs') {
            $admin->auditLogs();
        } else {
            $admin->users();
        }
        break;

    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}