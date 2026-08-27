<?php
// Configuration pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger l'autoload Composer (corrigé)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Start session early to avoid "headers already sent" warnings from views
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

try {
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
    case 'bilan':
        $controller = new App\Controllers\BilanController();
        if ($action === 'initial') {
            $controller->initial();
        } elseif ($action === 'view_copy') {
            $controller->viewCopy();
        } elseif ($action === 'export') {
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
    case 'payroll':
        $controller = new App\Controllers\PayrollController();
        if ($action === 'sites') {
            $controller->sites();
        } elseif ($action === 'createSite') {
            $controller->createSite();
        } elseif ($action === 'editSite') {
            $controller->editSite();
        } elseif ($action === 'siteDetail') {
            $controller->siteDetail();
        } elseif ($action === 'workers') {
            $controller->workers();
        } elseif ($action === 'createWorker') {
            $controller->createWorker();
        } elseif ($action === 'editWorker') {
            $controller->editWorker();
        } elseif ($action === 'deleteWorker') {
            $controller->deleteWorker();
        } elseif ($action === 'reactivateWorker') {
            $controller->reactivateWorker();
        } elseif ($action === 'importWorkers') {
            $controller->importWorkers();
        } elseif ($action === 'salaryConfig') {
            $controller->salaryConfig();
        } elseif ($action === 'attendance') {
            $controller->attendance();
        } elseif ($action === 'payslip') {
            $controller->payslip();
        } elseif ($action === 'savePayslip') {
            $controller->savePayslip();
        } elseif ($action === 'exportPayslipPDF') {
            $controller->exportPayslipPDF();
        } elseif ($action === 'exportPresencePDF') {
            $controller->exportPresencePDF();
        } elseif ($action === 'payslips') {
            $controller->payslips();
        } elseif ($action === 'archivePayslip') {
            $controller->archivePayslip();
        } elseif ($action === 'unarchivePayslip') {
            $controller->unarchivePayslip();
        } elseif ($action === 'deletePayslip') {
            $controller->deletePayslip();
        } elseif ($action === 'weeklyReportSynthesis') {
            $controller->weeklyReportSynthesis();
        } elseif ($action === 'exportWeeklySynthesisPDF') {
            $controller->exportWeeklySynthesisPDF();
        } else {
            $controller->sites();
        }
        break;
    case 'admin':
        $controller = new App\Controllers\AdminController();
        $controller->index();
        break;
    case 'home':
        (new App\Controllers\HomeController())->index();
        break;
    default:
        echo 'Page not found';
        break;
}
} catch (\Throwable $e) {
    error_log('Public index exception: ' . $e->getMessage());
    http_response_code(500);
    echo '<div style="padding:30px;font-family:Arial,sans-serif;">';
    echo '<h1>Erreur interne du serveur</h1>';

    $debugEnabled = filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
    if ($debugEnabled || (($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '') === 'development')) {
        echo '<pre style="white-space:pre-wrap;background:#f8f9fa;padding:16px;border-radius:8px;">' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<p>Impossible de se connecter à la base de données. Veuillez réessayer dans quelques instants.</p>';
    }

    echo '</div>';
}
