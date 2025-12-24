<?php
namespace App\Controllers;
use App\Core\Controller;

use App\Models\JournalModel;
class JournalController extends Controller
{
    public function delete()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new JournalModel();
            $model->delete($id);
        }
        header('Location: ?page=journal');
        exit;
    }

    public function edit()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $id = $_GET['id'] ?? null;
        $model = new JournalModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $data = [
                'date' => $_POST['date'] ?? '',
                'lieu' => trim($_POST['lieu'] ?? ''),
                'compte' => $_POST['compte'] ?? '',
                'libelle' => $_POST['libelle'] ?? '',
                'debit' => floatval($_POST['debit'] ?? 0),
                'credit' => floatval($_POST['credit'] ?? 0)
            ];
            $model->update($id, $data);
            header('Location: ?page=journal');
            exit;
        }
        $entry = null;
        if ($id) {
            $entry = $model->getById($id);
        }
        $entries = $model->getAll();
        $this->render('journal_edit', [
            'entry' => $entry,
            'entries' => $entries
        ]);
    }
    public function index()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $model = new JournalModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'lieu' => $_GET['lieu'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $entries = $model->getFiltered($filters);
        $this->render('journal', ['entries' => $entries, 'filters' => $filters]);
    }
    public function add()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                die('Erreur CSRF');
            }
            $date = $_POST['date'] ?? '';
            $lieu = trim($_POST['lieu'] ?? '');

            // accept either text inputs or selects for both comptes
            $compte_debit = trim($_POST['compte_debit'] ?? $_POST['compte_debitSelect'] ?? '');
            $intitule_debit = trim($_POST['intitule_debit'] ?? '');
            $compte_credit = trim($_POST['compte_credit'] ?? $_POST['compte_creditSelect'] ?? '');
            $intitule_credit = trim($_POST['intitule_credit'] ?? '');

            $libelle = trim($_POST['libelle'] ?? '');
            $debit = $_POST['debit'] ?? '';
            $credit = $_POST['credit'] ?? '';

            $errors = [];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
                $errors[] = 'Date invalide';
            if ($lieu === '' || strlen($lieu) > 64)
                $errors[] = 'Lieu invalide';
            if (strlen($compte_debit) < 1 || strlen($compte_debit) > 32)
                $errors[] = 'Compte débit invalide';
            if (strlen($compte_credit) < 1 || strlen($compte_credit) > 32)
                $errors[] = 'Compte crédit invalide';
            if (strlen($intitule_debit) < 1 || strlen($intitule_debit) > 64)
                $errors[] = 'Intitulé compte débit invalide';
            if (strlen($intitule_credit) < 1 || strlen($intitule_credit) > 64)
                $errors[] = 'Intitulé compte crédit invalide';
            if (strlen($libelle) < 1 || strlen($libelle) > 64)
                $errors[] = 'Libellé invalide';
            if ($debit === '' || !is_numeric($debit) || floatval($debit) <= 0)
                $errors[] = 'Débit invalide';
            if ($credit === '' || !is_numeric($credit) || floatval($credit) <= 0)
                $errors[] = 'Crédit invalide';

            // Montants doivent être égaux pour respecter la partie double
            if (empty($errors)) {
                if (abs(floatval($debit) - floatval($credit)) > 0.001) {
                    $errors[] = 'Le montant débit doit être égal au montant crédit';
                }
            }

            if ($errors) {
                die(implode('<br>', $errors));
            }

            $journal = new JournalModel();

            // écriture débit
            $journal->insert([
                'date' => $date,
                'lieu' => $lieu,
                'compte' => $compte_debit,
                'intitule' => $intitule_debit,
                'libelle' => $libelle,
                'debit' => floatval($debit),
                'credit' => 0
            ]);

            // écriture crédit
            $journal->insert([
                'date' => $date,
                'lieu' => $lieu,
                'compte' => $compte_credit,
                'intitule' => $intitule_credit,
                'libelle' => $libelle,
                'debit' => 0,
                'credit' => floatval($credit)
            ]);

            // Mise à jour de la fiche de stock pour chaque compte
            $stockModel = new \App\Models\StockModel();
            $stockModel->addOrUpdate($compte_debit, $intitule_debit, $debit, 0);
            $stockModel->addOrUpdate($compte_credit, $intitule_credit, 0, $credit);

            header('Location: ?page=journal');
            exit;
        }

        // Récupérer les comptes pour le select
        $grandLivreModel = new \App\Models\GrandLivreModel();
        $comptes = $grandLivreModel->getComptes();
        // Pour chaque compte, récupérer l'intitulé
        $comptesList = [];
        foreach ($comptes as $no) {
            $entry = $grandLivreModel->getByCompte($no);
            $intitule = isset($entry[0]['intitule']) ? $entry[0]['intitule'] : $no;
            $comptesList[] = ['no' => $no, 'intitule' => $intitule];
        }
        $this->render('journal', ['comptes' => $comptesList]);
    }
}