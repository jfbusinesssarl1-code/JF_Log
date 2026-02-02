<?php
// Configuration pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger l'autoload Composer (corrigé)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists(filename: $autoload)) {
    require_once $autoload;
}

// Simple router
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? '';

// Routes API
if ($page === 'api') {
    $apiAction = $_GET['action'] ?? '';
    $apiController = new App\Controllers\ApiController();

    if ($apiAction === 'comptes') {
        $apiController->getComptes();
    }
    exit;
}

switch ($page) {
    case 'stock':
        $controller = new App\Controllers\StockController();
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'delete') {
            $controller->delete();
        } elseif ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'login':
        (new App\Controllers\LoginController())->index();
        break;
    case 'signup':
        (new App\Controllers\SignupController())->index();
        break;
    case 'dashboard':
        $controller = new App\Controllers\DashboardController();
        if ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'register':
        (new App\Controllers\RegisterController())->index();
        break;
    case 'logout':
        (new App\Controllers\LogoutController())->index();
        break;
    case 'caisse':
        $controller = new App\Controllers\CaisseController();
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'delete') {
            $controller->delete();
        } elseif ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'journal':
        $controller = new App\Controllers\JournalController();
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'delete') {
            $controller->delete();
        } elseif ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'grandlivre':
        $controller = new App\Controllers\GrandLivreController();
        if ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'delete') {
            $controller->delete();
        } elseif ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'balance':
        $controller = new App\Controllers\BalanceController();
        if ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    case 'releve':
        $controller = new App\Controllers\ReleveController();
        if ($action === 'export') {
            $controller->export();
        } else {
            $controller->index();
        }
        break;
    default:
        echo 'Page not found';
        break;
}
