<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\BilanModel;
use App\Models\CompteModel;

class BilanController extends Controller
{
    public function index()
    {
        $this->requireRole(['admin', 'accountant', 'manager', 'comptable']);

        $model = new BilanModel();
        $action = $_GET['action'] ?? 'view';

        if ($action === 'add_account') {
            $this->addAccount();
            return;
        } elseif ($action === 'update_account') {
            $this->updateAccount();
            return;
        } elseif ($action === 'edit_account') {
            $this->editAccount();
            return;
        } elseif ($action === 'remove_account') {
            $this->removeAccount();
            return;
        } elseif ($action === 'save_periodic') {
            $this->savePeriodicCopy();
            return;
        } elseif ($action === 'archive_copy') {
            $this->archiveCopy();
            return;
        } elseif ($action === 'restore_copy') {
            $this->restoreCopy();
            return;
        } elseif ($action === 'delete_copy') {
            $this->deleteCopy();
            return;
        } elseif ($action === 'copies') {
            $this->showCopies();
            return;
        }

        // Default: show current balance
        $currentBilan = $model->getCurrentBilan();
        $structure = $model->getBilanStructure($currentBilan);

        $this->render('bilan/view', [
            'bilan' => $currentBilan,
            'structure' => $structure,
            'type' => 'current'
        ]);
    }

    public function initial()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $model = new BilanModel();
        
        // Clean up old invalid documents on first access
        $model->cleanupInvalidDocuments();
        
        $initialBilan = $model->getInitialBilan();

        if (!$initialBilan) {
            $initialBilan = [
                'title' => 'Bilan Initial',
                'date' => date('Y-m-d'),
                'accounts' => []
            ];
        }

        $structure = $model->getBilanStructure($initialBilan);

        $this->render('bilan/initial', [
            'bilan' => $initialBilan,
            'structure' => $structure
        ]);
    }

    private function addAccount()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        $model = new BilanModel();
        $compteModel = new CompteModel();

        $title = $_POST['title'] ?? 'Bilan Initial';
        $date = $_POST['date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'actif';
        $category = $_POST['category'] ?? '';
        $accountCode = $_POST['account_code'] ?? '';
        $value = (float) ($_POST['value'] ?? 0);

        // Validation
        if (empty($accountCode)) {
            $_SESSION['error'] = 'Code compte obligatoire.';
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        // Get account name
        $allComptes = $compteModel->getAll();
        $accountName = '';
        foreach ($allComptes as $compte) {
            if ($compte['code'] === $accountCode) {
                $accountName = $compte['intitule'];
                break;
            }
        }

        $accountData = [
            'code' => $accountCode,
            'name' => $accountName,
            'type' => $type,
            'category' => $category,
            'value' => $value,
            'debit' => $type === 'actif' ? $value : 0,
            'credit' => $type === 'passif' ? $value : 0,
            'solde' => $type === 'actif' ? $value : -$value
        ];

        try {
            if ($model->addAccountToInitial($accountData)) {
                $_SESSION['success'] = 'Compte ajouté au bilan initial avec succès.';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout du compte.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            error_log('BilanController::addAccount error: ' . $e->getMessage());
        }

        header('Location: ?page=bilan&action=initial');
        exit;
    }

    private function updateAccount()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        $model = new BilanModel();
        $compteModel = new CompteModel();

        $oldAccountCode = $_POST['old_account_code'] ?? '';
        $title = $_POST['title'] ?? 'Bilan Initial';
        $date = $_POST['date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'actif';
        $category = $_POST['category'] ?? '';
        $accountCode = $_POST['account_code'] ?? '';
        $value = (float) ($_POST['value'] ?? 0);

        // Get account name
        $allComptes = $compteModel->getAll();
        $accountName = '';
        foreach ($allComptes as $compte) {
            if ($compte['code'] === $accountCode) {
                $accountName = $compte['intitule'];
                break;
            }
        }

        if (!empty($oldAccountCode) && $oldAccountCode !== $accountCode) {
            $model->removeAccountFromInitial($oldAccountCode);
        }

        $accountData = [
            'code' => $accountCode,
            'name' => $accountName,
            'type' => $type,
            'category' => $category,
            'value' => $value,
            'debit' => $type === 'actif' ? $value : 0,
            'credit' => $type === 'passif' ? $value : 0,
            'solde' => $type === 'actif' ? $value : -$value
        ];

        if ($model->addAccountToInitial($accountData)) {
            $_SESSION['success'] = 'Compte modifié dans le bilan initial avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification du compte.';
        }

        header('Location: ?page=bilan&action=initial');
        exit;
    }

    private function editAccount()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $accountCode = $_GET['code'] ?? '';
        if (!$accountCode) {
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        $model = new BilanModel();
        $initialBilan = $model->getInitialBilan();
        $selectedAccount = null;
        foreach ($initialBilan['accounts'] ?? [] as $account) {
            if ($account['code'] === $accountCode) {
                $selectedAccount = $account;
                break;
            }
        }

        if (!$selectedAccount) {
            $_SESSION['error'] = 'Compte non trouvé pour modification.';
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        $structure = $model->getBilanStructure($initialBilan);

        $this->render('bilan/initial', [
            'bilan' => $initialBilan,
            'structure' => $structure,
            'selectedAccount' => $selectedAccount
        ]);
    }

    private function removeAccount()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $accountCode = $_GET['code'] ?? '';
        if (!$accountCode) {
            header('Location: ?page=bilan&action=initial');
            exit;
        }

        $model = new BilanModel();
        if ($model->removeAccountFromInitial($accountCode)) {
            $_SESSION['success'] = 'Compte supprimé du bilan initial avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du compte.';
        }

        header('Location: ?page=bilan&action=initial');
        exit;
    }

    private function savePeriodicCopy()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=bilan');
            exit;
        }

        $title = $_POST['copy_title'] ?? '';
        $date = $_POST['copy_date'] ?? date('Y-m-d');

        $model = new BilanModel();
        $id = $model->savePeriodicCopy($title, $date);

        if ($id) {
            $_SESSION['success'] = 'Copie périodique sauvegardée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la sauvegarde de la copie périodique.';
        }

        header('Location: ?page=bilan');
        exit;
    }

    private function archiveCopy()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $id = $_GET['id'] ?? '';
        if (!$id) {
            header('Location: ?page=bilan&action=copies');
            exit;
        }

        $model = new BilanModel();
        if ($model->archivePeriodicCopy($id)) {
            $_SESSION['success'] = 'Copie archivée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'archivage de la copie.';
        }

        header('Location: ?page=bilan&action=copies');
        exit;
    }

    private function restoreCopy()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $id = $_GET['id'] ?? '';
        if (!$id) {
            header('Location: ?page=bilan&action=copies');
            exit;
        }

        $model = new BilanModel();
        if ($model->restorePeriodicCopy($id)) {
            $_SESSION['success'] = 'Copie restaurée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la restauration de la copie.';
        }

        header('Location: ?page=bilan&action=copies');
        exit;
    }

    private function deleteCopy()
    {
        $this->requireRole(['admin', 'accountant', 'comptable']);

        $id = $_GET['id'] ?? '';
        if (!$id) {
            header('Location: ?page=bilan&action=copies');
            exit;
        }

        $model = new BilanModel();
        if ($model->deletePeriodicCopy($id)) {
            $_SESSION['success'] = 'Copie supprimée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression de la copie.';
        }

        header('Location: ?page=bilan&action=copies');
        exit;
    }

    private function showCopies()
    {
        $this->requireRole(['admin', 'accountant', 'manager', 'comptable']);

        $model = new BilanModel();
        $copies = $model->getPeriodicCopies();

        $this->render('bilan/copies', [
            'copies' => $copies
        ]);
    }

    public function viewCopy()
    {
        $this->requireRole(['admin', 'accountant', 'manager', 'comptable']);

        $id = $_GET['id'] ?? '';
        if (!$id) {
            header('Location: ?page=bilan&action=copies');
            exit;
        }

        $model = new BilanModel();
        $copy = $model->getPeriodicCopy($id);

        if (!$copy) {
            $_SESSION['error'] = 'Copie non trouvée.';
            header('Location: ?page=bilan&action=copies');
            exit;
        }

        $structure = $model->getBilanStructure($copy);

        $this->render('bilan/view', [
            'bilan' => $copy,
            'structure' => $structure,
            'type' => 'copy'
        ]);
    }

    public function export()
    {
        $this->requireRole(['admin', 'accountant', 'manager', 'comptable']);
        $format = $_GET['format'] ?? 'pdf';
        $type = $_GET['type'] ?? 'current'; // 'current', 'initial', or 'copy'
        $copyId = $_GET['copy_id'] ?? '';

        if ($format !== 'pdf') {
            $format = 'pdf';
        }

        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "DEBUG export: class_exists Mpdf? " . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no') . "\n";
            echo "PHP SAPI: " . PHP_SAPI . "\n";
            echo "User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'n/a') . "\n";
            exit;
        }

        $model = new BilanModel();
        $bilan = null;
        $title = '';
        $exportDate = date('d/m/Y H:i');

        // Validation: type must be 'initial' or 'copy'
        if ($type === 'current') {
            throw new \Exception("L'export du bilan en cours n'est pas autorisé. Vous pouvez exporter le bilan initial ou une copie périodique.");
        }

        if ($type === 'initial') {
            $bilan = $model->getInitialBilan();
            if ($bilan) {
                $title = $bilan['title'] ?? 'Bilan Initial';
                $savedDate = isset($bilan['date']) ? date('d/m/Y', strtotime($bilan['date'])) : $exportDate;
                if (isset($bilan['updated_at']) && $bilan['updated_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                    $savedDate = $bilan['updated_at']->toDateTime()->format('d/m/Y H:i');
                } elseif (isset($bilan['created_at']) && $bilan['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                    $savedDate = $bilan['created_at']->toDateTime()->format('d/m/Y H:i');
                }
                error_log("EXPORT INITIAL: Title='$title', SavedDate='$savedDate'");
            } else {
                // Fallback si pas de bilan initial
                $bilan = [
                    'title' => 'Bilan Initial',
                    'date' => date('Y-m-d'),
                    'accounts' => []
                ];
                $title = $bilan['title'];
                $savedDate = date('d/m/Y');
                error_log("EXPORT INITIAL: No initial bilan found, using fallback");
            }
        } elseif ($type === 'copy') {
            if (empty($copyId)) {
                throw new \Exception("L'ID de la copie périodique est requis pour l'export. Paramètre manquant: copy_id");
            }
            $bilan = $model->getPeriodicCopy($copyId);
            if (!$bilan) {
                throw new \Exception("Copie périodique non trouvée: $copyId");
            }
            $title = $bilan['title'] ?? 'Copie Périodique';
            $savedDate = isset($bilan['date']) ? $bilan['date'] : $exportDate;
            if (isset($bilan['created_at']) && $bilan['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                $savedDate = $bilan['created_at']->toDateTime()->format('d/m/Y H:i');
            }
            error_log("EXPORT COPY: ID='$copyId', Title='$title', SavedDate='$savedDate'");
        } else {
            throw new \Exception("Type d'export invalide: '$type'. Types autorisés: initial, copy");
        }

        if (!$bilan) {
            throw new \Exception("Bilan non trouvé.");
        }

        $structure = $model->getBilanStructure($bilan);

        // Use PdfHelper for consistent header
        $header = \App\Helpers\PdfHelper::renderHeader($title);

        $html = $header;



        // Helper function for formatting values (same as in view)
        $formatValue = function($value) {
            if ($value < 0) {
                return '<span style="color:#dc3545;font-weight:600;">(' . number_format(abs($value), 2, ',', ' ') . ')</span>';
            }
            return number_format($value, 2, ',', ' ');
        };

        // ACTIF Section
        $html .= '<div style="margin-bottom:20px;">';
        $html .= '<h2 style="background:#28a745;color:white;padding:8px;margin:0 0 12px 0;text-align:center;font-size:16px;font-weight:bold;">ACTIF</h2>';

        // Actif Immobilisé
        if (!empty($structure['actif']['immobilise']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#f0f8f5;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #28a745;color:#28a745;font-size:12px;font-weight:bold;">Actif Immobilisé</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#e9ecef;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['actif']['immobilise']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#e9ecef;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Actif Immobilisé</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['actif']['immobilise']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Actif Circulant
        if (!empty($structure['actif']['circulant']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#f0f8f5;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #28a745;color:#28a745;font-size:12px;font-weight:bold;">Actif Circulant</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#e9ecef;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['actif']['circulant']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#e9ecef;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Actif Circulant</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['actif']['circulant']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Trésorerie Actif
        if (!empty($structure['actif']['tresorerie']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#f0f8f5;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #28a745;color:#28a745;font-size:12px;font-weight:bold;">Trésorerie Actif</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#e9ecef;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['actif']['tresorerie']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#e9ecef;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Trésorerie Actif</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['actif']['tresorerie']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';;
            $html .= '</div>';
        }

        // Total Actif
        $html .= '<div style="background:#28a745;color:white;padding:10px;text-align:center;font-size:14px;font-weight:bold;margin:10px 0;">';
        $html .= 'TOTAL ACTIF: ' . $formatValue($structure['actif']['total']);
        $html .= '</div>';

        $html .= '</div>'; // End ACTIF

        // PASSIF Section
        $html .= '<div style="margin-bottom:20px;">';
        $html .= '<h2 style="background:#dc3545;color:white;padding:8px;margin:0 0 12px 0;text-align:center;font-size:16px;font-weight:bold;">PASSIF</h2>';

        // Capitaux Propres
        if (!empty($structure['passif']['capitaux_propres']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#ffe9e9;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #dc3545;color:#dc3545;font-size:12px;font-weight:bold;">Capitaux Propres</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#ffe9e9;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['passif']['capitaux_propres']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#ffe9e9;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Capitaux Propres</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['passif']['capitaux_propres']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Passif Non Courant
        if (!empty($structure['passif']['non_courant']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#fff3e6;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #fd7e14;color:#fd7e14;font-size:12px;font-weight:bold;">Passif Non Courant</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#fff3e6;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['passif']['non_courant']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#fff3e6;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Passif Non Courant</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['passif']['non_courant']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Passif Circulant
        if (!empty($structure['passif']['circulant']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#e7f3ff;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #17a2b8;color:#17a2b8;font-size:12px;font-weight:bold;">Passif Circulant</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#e7f3ff;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['passif']['circulant']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#e7f3ff;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Passif Circulant</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['passif']['circulant']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Trésorerie Passif
        if (!empty($structure['passif']['tresorerie']['accounts'])) {
            $html .= '<div style="margin-bottom:15px;">';
            $html .= '<h3 style="background:#f0f0f0;padding:6px 8px;margin:0 0 8px 0;border-left:3px solid #6c757d;color:#6c757d;font-size:12px;font-weight:bold;">Trésorerie Passif</h3>';
            $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:10px;" border="0" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr style="background:#f0f0f0;border-bottom:1px solid #ccc;font-weight:bold;">';
            $html .= '<th style="width:60%;text-align:left;padding:4px;">Intitulé</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Débit</th>';
            $html .= '<th style="width:13%;text-align:right;padding:4px;">Crédit</th>';
            $html .= '<th style="width:14%;text-align:right;padding:4px;">Solde</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($structure['passif']['tresorerie']['accounts'] as $account) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                $html .= '<td style="padding:4px;">' . htmlspecialchars($account['name']) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['debit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;">' . $formatValue($account['credit'] ?? 0) . '</td>';
                $html .= '<td style="text-align:right;padding:4px;font-weight:bold;">' . $formatValue($account['solde'] ?? 0) . '</td>';
                $html .= '</tr>';
            }

            $html .= '<tr style="background:#f0f0f0;font-weight:bold;font-size:11px;border-top:1px solid #ccc;">';
            $html .= '<td style="padding:4px;font-weight:bold;">Total Trésorerie Passif</td>';
            $html .= '<td colspan="3" style="text-align:right;padding:4px;">' . $formatValue($structure['passif']['tresorerie']['total']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        // Total Passif
        $html .= '<div style="background:#dc3545;color:white;padding:10px;text-align:center;font-size:14px;font-weight:bold;margin:10px 0;">';
        $html .= 'TOTAL PASSIF: ' . $formatValue($structure['passif']['total']);
        $html .= '</div>';

        $html .= '</div>'; // End PASSIF

        // PDF export
        if ($format === 'pdf') {
            error_log('DEBUG: Bilan export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'orientation' => 'P', // Portrait orientation
                        'margin_left' => 15,
                        'margin_right' => 15,
                        'margin_top' => 20,
                        'margin_bottom' => 20,
                        'margin_header' => 10,
                        'margin_footer' => 10
                    ]);
                    $mpdf->WriteHTML($html);
                    $filename = 'bilan_' . $type . '_' . date('Y-m-d_H-i-s') . '.pdf';
                    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
                    exit;
                } catch (\Throwable $e) {
                    error_log('ERROR: mpdf generation failed: ' . $e->getMessage());
                }
            } else {
                error_log('DEBUG: mpdf not available, using HTML fallback');
            }
        }

        // Fallback: HTML
        $notice = '<div style="background:#fff3cd;padding:10px;border:1px solid #ffeeba;margin-bottom:10px;">' .
            '<strong>Remarque :</strong> Le générateur PDF côté serveur (mpdf) n\'est pas installé. Pour obtenir un vrai PDF, installez <code>mpdf/mpdf</code> via Composer (ex: <code>composer require mpdf/mpdf</code>).' .
            '</div>';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="bilan_' . $type . '_' . date('Y-m-d_H-i-s') . '.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}