<?php
namespace App\Controllers;
use App\Core\Controller;

use App\Models\JournalModel;
use App\Models\CompteModel;
class JournalController extends Controller
{
    public function delete()
    {
        $this->requireRole(['accountant', 'admin']);
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
        $this->requireRole(['accountant', 'admin']);
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

    // Retourne uniquement le <tbody> (utilisé pour refresh partiel via AJAX)
    public function list_partial()
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
        header('Content-Type: text/html; charset=utf-8');
        header('X-Row-Count: ' . count($entries));
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $id = isset($entry['_id']) ? (string) $entry['_id'] : '';
                $date = htmlspecialchars($entry['date'] ?? '');
                $compte = htmlspecialchars($entry['compte'] ?? '');
                $lieu = htmlspecialchars($entry['lieu'] ?? '');
                $libelle = htmlspecialchars($entry['libelle'] ?? '');
                $debit = htmlspecialchars($entry['debit'] ?? '');
                $credit = htmlspecialchars($entry['credit'] ?? '');
                echo "<tr>\n";
                echo "<td>$date</td>\n";
                echo "<td>$compte</td>\n";
                echo "<td>$lieu</td>\n";
                echo "<td>$libelle</td>\n";
                echo "<td>$debit</td>\n";
                echo "<td>$credit</td>\n";
                if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin'])) {
                    echo "<td class=\"d-flex justify-content-center gap-1\"> <a href=\"?page=journal&action=edit&id=$id\" class=\"btn btn-sm btn-warning\">Modifier</a> <a href=\"?page=journal&action=delete&id=$id\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Confirmer la suppression ?');\">Supprimer</a> </td>\n";
                } else {
                    echo "<td>—</td>\n";
                }
                echo "</tr>\n";
            }
        } else {
            echo '<tr><td colspan="7" class="text-center">Aucune opération</td></tr>';
        }
        exit;
    }
    public function add()
    {
        $this->requireRole(['accountant', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
                    exit;
                }
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
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    exit;
                }
                die(implode('<br>', $errors));
            }

            try {
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

                // Si le compte n'existe pas dans PLAN.xlsx, essayer de l'ajouter (non bloquant)
                try {
                    $cm = new CompteModel();
                    $cm->addIfMissing($compte_debit, $intitule_debit);
                    $cm->addIfMissing($compte_credit, $intitule_credit);
                } catch (\Throwable $e) {
                    error_log('JournalController: ajout compte au PLAN.xlsx échoué: ' . $e->getMessage());
                }

                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => true]);
                    exit;
                }

                header('Location: ?page=journal');
                exit;

            } catch (\Throwable $e) {
                error_log('JournalController::add exception: ' . $e->getMessage());
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
                    exit;
                }
                throw $e;
            }
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

    public function export()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $format = $_GET['format'] ?? 'pdf';
        if ($format !== 'pdf')
            $format = 'pdf';
        // Debug helper
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "DEBUG export: class_exists Mpdf? " . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no') . "\n";
            echo "PHP SAPI: " . PHP_SAPI . "\n";
            echo "User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'n/a') . "\n";
            exit;
        }

        $model = new JournalModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'lieu' => $_GET['lieu'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $items = $model->getFiltered($filters);

        $header = \App\Helpers\PdfHelper::renderHeader('Journal Comptable');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Compte</th><th>Lieu</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
        foreach ($items as $e) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['compte'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['lieu'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['debit'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['credit'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: Journal export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('journal.pdf', \Mpdf\Output\Destination::DOWNLOAD);
                    exit;
                } catch (\Throwable $e) {
                    error_log('ERROR: mpdf generation failed: ' . $e->getMessage());
                }
            } else {
                error_log('DEBUG: mpdf not available, using HTML fallback');
            }
        }

        $notice = '<div style="background:#fff3cd;padding:10px;border:1px solid #ffeeba;margin-bottom:10px;">' .
            '<strong>Remarque :</strong> Le générateur PDF côté serveur (mpdf) n\'est pas installé. Pour obtenir un vrai PDF, installez <code>mpdf/mpdf</code> via Composer (ex: <code>composer require mpdf/mpdf</code>).' .
            '</div>';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="journal.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}