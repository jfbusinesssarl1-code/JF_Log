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

        if ($model->addAccountToInitial($accountData)) {
            $_SESSION['success'] = 'Compte ajouté au bilan initial avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du compte.';
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
}