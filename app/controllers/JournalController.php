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
        $token = $_GET['token'] ?? '';
        if (!\App\Core\Csrf::checkToken($token)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
        } elseif ($id) {
            $model = new JournalModel();
            $model->delete($id);
        }
        // Maintain filters after delete
        $params = [];
        if (isset($_GET['compte'])) $params['compte'] = $_GET['compte'];
        if (isset($_GET['lieu'])) $params['lieu'] = $_GET['lieu'];
        if (isset($_GET['date_debut'])) $params['date_debut'] = $_GET['date_debut'];
        if (isset($_GET['date_fin'])) $params['date_fin'] = $_GET['date_fin'];
        if (isset($_GET['page_num'])) $params['page_num'] = $_GET['page_num'];
        $query = http_build_query($params);
        header('Location: ?page=journal' . (!empty($query) ? '&' . $query : ''));
        exit;
    }
    public function edit()
    {
        $this->requireRole(['accountant', 'admin']);
        $id = $_GET['id'] ?? null;
        $model = new JournalModel();
        $entry = null;
        if ($id) {
            $entry = $model->getById($id);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
                // Maintain filters after CSRF error
                $params = [];
                if (isset($_GET['compte'])) $params['compte'] = $_GET['compte'];
                if (isset($_GET['lieu'])) $params['lieu'] = $_GET['lieu'];
                if (isset($_GET['date_debut'])) $params['date_debut'] = $_GET['date_debut'];
                if (isset($_GET['date_fin'])) $params['date_fin'] = $_GET['date_fin'];
                if (isset($_GET['page_num'])) $params['page_num'] = $_GET['page_num'];
                $query = http_build_query($params);
                header('Location: ?page=journal' . (!empty($query) ? '&' . $query : ''));
                exit;
            }
            
            $quantite = $_POST['quantite'] ?? '';
            $prixUnitaire = $_POST['prix_unitaire'] ?? '';
            $montant = floatval($quantite) * floatval($prixUnitaire);
            $side = $_POST['side'] ?? '';
            if ($side !== 'debit' && $side !== 'credit') {
                $side = ($entry && isset($entry['debit']) && floatval($entry['debit']) > 0) ? 'debit' : 'credit';
            }
            $data = [
                'date' => $_POST['date'] ?? '',
                'lieu' => trim($_POST['lieu'] ?? ''),
                'compte' => $_POST['compte'] ?? '',
                'libelle' => $_POST['libelle'] ?? '',
                'quantite' => floatval($quantite),
                'prix_unitaire' => floatval($prixUnitaire),
                'prix_global' => $montant,
                'debit' => $side === 'debit' ? $montant : 0,
                'credit' => $side === 'credit' ? $montant : 0
            ];
            $model->update($id, $data);
            // Maintain filters after update
            $params = [];
            if (isset($_GET['compte'])) $params['compte'] = $_GET['compte'];
            if (isset($_GET['lieu'])) $params['lieu'] = $_GET['lieu'];
            if (isset($_GET['date_debut'])) $params['date_debut'] = $_GET['date_debut'];
            if (isset($_GET['date_fin'])) $params['date_fin'] = $_GET['date_fin'];
            if (isset($_GET['page_num'])) $params['page_num'] = $_GET['page_num'];
            $query = http_build_query($params);
            header('Location: ?page=journal' . (!empty($query) ? '&' . $query : ''));
            exit;
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

        // Déterminer le nombre de pages pour afficher la dernière par défaut
        $itemsPerPage = 20;

        // Construire la requête pour compter
        $query = [];
        if (!empty($filters['compte'])) {
            $query['compte'] = ['$regex' => $filters['compte'], '$options' => 'i'];
        }
        if (!empty($filters['lieu'])) {
            $query['lieu'] = ['$regex' => $filters['lieu'], '$options' => 'i'];
        }
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut'])) {
                $dateQuery['$gte'] = $filters['date_debut'];
            }
            if (!empty($filters['date_fin'])) {
                $dateQuery['$lte'] = $filters['date_fin'];
            }
            if (!empty($dateQuery)) {
                $query['date'] = $dateQuery;
            }
        }

        $totalCount = $model->countDocuments($query);
        $totalPages = ceil($totalCount / $itemsPerPage);

        // Si page_num n'est pas fourni, afficher la dernière page
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : max(1, $totalPages);

        $result = $model->getFilteredWithPagination($filters, $page, $itemsPerPage);
        $entries = $result['entries'];
        $pagination = $result['pagination'];

        // compute totals (debit, credit) from the filtered entries
        $totals = ['debit' => 0.0, 'credit' => 0.0];
        foreach ($entries as $e) {
            $totals['debit'] += isset($e['debit']) && is_numeric($e['debit']) ? floatval($e['debit']) : 0.0;
            $totals['credit'] += isset($e['credit']) && is_numeric($e['credit']) ? floatval($e['credit']) : 0.0;
        }

        $this->render('journal', [
            'entries' => $entries,
            'filters' => $filters,
            'totals' => $totals,
            'pagination' => $pagination
        ]);
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
            $quantite = $_POST['quantite'] ?? '';
            $prixUnitaire = $_POST['prix_unitaire'] ?? '';
            $montant = floatval($quantite) * floatval($prixUnitaire);

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
            if ($quantite === '' || !is_numeric($quantite) || floatval($quantite) <= 0)
                $errors[] = 'Quantité invalide';
            if ($prixUnitaire === '' || !is_numeric($prixUnitaire) || floatval($prixUnitaire) <= 0)
                $errors[] = 'Prix unitaire invalide';

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
                    'quantite' => floatval($quantite),
                    'prix_unitaire' => floatval($prixUnitaire),
                    'prix_global' => $montant,
                    'debit' => $montant,
                    'credit' => 0
                ]);

                // écriture crédit
                $journal->insert([
                    'date' => $date,
                    'lieu' => $lieu,
                    'compte' => $compte_credit,
                    'intitule' => $intitule_credit,
                    'libelle' => $libelle,
                    'quantite' => floatval($quantite),
                    'prix_unitaire' => floatval($prixUnitaire),
                    'prix_global' => $montant,
                    'debit' => 0,
                    'credit' => $montant
                ]);

                // Mise à jour de la fiche de stock pour chaque compte
                $stockModel = new \App\Models\StockModel();
                $stockModel->addOrUpdate($compte_debit, $intitule_debit, $montant, 0);
                $stockModel->addOrUpdate($compte_credit, $intitule_credit, 0, $montant);

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

                // Maintain filters after add
                $params = [];
                if (isset($_GET['compte'])) $params['compte'] = $_GET['compte'];
                if (isset($_GET['lieu'])) $params['lieu'] = $_GET['lieu'];
                if (isset($_GET['date_debut'])) $params['date_debut'] = $_GET['date_debut'];
                if (isset($_GET['date_fin'])) $params['date_fin'] = $_GET['date_fin'];
                if (isset($_GET['page_num'])) $params['page_num'] = $_GET['page_num'];
                $query = http_build_query($params);
                header('Location: ?page=journal' . (!empty($query) ? '&' . $query : ''));
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
            'date_fin' => $_GET['date_fin'] ?? '',
            'only_debit' => true
        ];
        $maxItems = 2000;
        $query = [];
        $query['debit'] = ['$gt' => 0];
        if (!empty($filters['compte'])) {
            $query['compte'] = ['$regex' => $filters['compte'], '$options' => 'i'];
        }
        if (!empty($filters['lieu'])) {
            $query['lieu'] = ['$regex' => $filters['lieu'], '$options' => 'i'];
        }
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut'])) {
                $dateQuery['$gte'] = $filters['date_debut'];
            }
            if (!empty($filters['date_fin'])) {
                $dateQuery['$lte'] = $filters['date_fin'];
            }
            if (!empty($dateQuery)) {
                $query['date'] = $dateQuery;
            }
        }
        $totalCount = $model->countDocuments($query);
        $items = $model->getFilteredLimited($filters, $maxItems);

        $header = \App\Helpers\PdfHelper::renderHeader('RAPPORT DES OPERATIONS JOURNALIERES');
        // Tighten header spacing for better first-page density
        $header = str_replace('padding:8px 0;', 'padding:1px 0;', $header);
        $header = str_replace('margin:8px 0 12px 0', 'margin:2px 0 6px 0', $header);
        $header = str_replace('margin:6px 0 12px 0', 'margin:2px 0 6px 0', $header);
        $header = str_replace('font-weight:700;margin:6px 0 12px 0', 'font-weight:700;font-size:14px;margin:2px 0 6px 0', $header);
        $footer = \App\Helpers\PdfHelper::renderFooter();

        $html = $header;

        // Afficher les filtres actifs si présents
        $activeFilters = [];
        if (!empty($filters['date_debut']))
            $activeFilters[] = 'Depuis: ' . htmlspecialchars($filters['date_debut']);
        if (!empty($filters['date_fin']))
            $activeFilters[] = 'Jusqu\'au: ' . htmlspecialchars($filters['date_fin']);
        if (!empty($filters['compte']))
            $activeFilters[] = 'Compte: ' . htmlspecialchars($filters['compte']);
        if (!empty($filters['lieu']))
            $activeFilters[] = 'Lieu: ' . htmlspecialchars($filters['lieu']);

        if (!empty($activeFilters)) {
            $html .= '<div style="background:#f8f9fa;padding:6px;margin-bottom:8px;border:1px solid #dee2e6;border-radius:4px;font-size:10px;">';
            $html .= '<strong>Filtres appliqués:</strong> ' . implode(' | ', $activeFilters);
            $html .= '</div>';
        }

        if ($totalCount > $maxItems) {
            $html .= '<div style="background:#fff3cd;padding:6px;margin-bottom:8px;border:1px solid #ffeeba;border-radius:4px;font-size:10px;">';
            $html .= '<strong>Attention :</strong> export limité à ' . $maxItems . ' lignes sur ' . $totalCount . '. ';
            $html .= 'Veuillez filtrer par période pour un export complet.';
            $html .= '</div>';
        }

        // Filter items to exclude accounts starting with 42 and 78
        $filteredItems = [];
        foreach ($items as $e) {
            $compte = $e['compte'] ?? '';
            if (strpos($compte, '42') !== 0 && strpos($compte, '78') !== 0) {
                $filteredItems[] = $e;
            }
        }

        // Paginate manually for subtotals per page (first page: 30, others: 40)
        $pages = [];
        $firstPageLines = 30;
        $otherPageLines = 40;
        $offset = 0;
        $totalItems = count($filteredItems);
        if ($totalItems > 0) {
            $pages[] = array_slice($filteredItems, $offset, $firstPageLines);
            $offset += $firstPageLines;
            while ($offset < $totalItems) {
                $pages[] = array_slice($filteredItems, $offset, $otherPageLines);
                $offset += $otherPageLines;
            }
        }
        $totalPages = count($pages);
        $sumGlobal = 0.0;

        foreach ($pages as $pageIndex => $pageItems) {
            $isLastPage = ($pageIndex === $totalPages - 1);
            $pageSubtotal = 0.0;

            // Start table for this page
            $html .= '<table style="width:100%;border-collapse:collapse;font-size:12px;" border="1" cellpadding="4" cellspacing="0">';
            $html .= '<thead><tr><th>Date</th><th>Lieu</th><th>Libellé</th><th>Quantité</th><th>Prix Unitaire</th><th>Prix Global</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($pageItems as $e) {
                $q = isset($e['quantite']) && is_numeric($e['quantite']) ? floatval($e['quantite']) : 0.0;
                $pu = isset($e['prix_unitaire']) && is_numeric($e['prix_unitaire']) ? floatval($e['prix_unitaire']) : 0.0;
                $pg = ($q > 0 && $pu > 0) ? ($q * $pu) : (isset($e['debit']) ? floatval($e['debit']) : 0.0);
                $pageSubtotal += $pg;
                $sumGlobal += $pg;

                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($e['lieu'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
                $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($q, 2, '.', '')) . '</td>';
                $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($pu, 2, '.', '')) . '</td>';
                $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($pg, 2, '.', '')) . '</td>';
                $html .= '</tr>';
            }

            // Add subtotal row for this page
            $html .= '<tr style="font-weight:700;background:#e9ecef;">';
            $html .= '<td colspan="5">Sous-total (Page ' . ($pageIndex + 1) . ')</td>';
            $html .= '<td style="text-align:right">' . number_format($pageSubtotal, 2, '.', '') . '</td>';
            $html .= '</tr>';

            // Add grand total on the last page
            if ($isLastPage) {
                $html .= '<tr style="font-weight:700;background:#f1f3f5;">';
                $html .= '<td colspan="5">TOTAL GLOBAL</td>';
                $html .= '<td style="text-align:right">' . number_format($sumGlobal, 2, '.', '') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            // Add page break between pages (except after the last page)
            if (!$isLastPage) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
        }

        $htmlWithFooter = $html . $footer;

        if ($format === 'pdf') {
            error_log('DEBUG: Journal export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'margin_top' => 6,
                        'margin_bottom' => 20,
                        'margin_left' => 10,
                        'margin_right' => 10
                    ]);
                    
                    // Define a simple footer that repeats on every page
                    $mpdf->SetHTMLFooter('
                        <table width="100%" style="border-top:1px solid #000;padding-top:4px;font-size:11px;">
                            <tr>
                                <td width="50%" style="text-align:left;">© 2024 JF BUSINESS SARL</td>
                                <td width="50%" style="text-align:right;">Page {PAGENO} / {nbpg}</td>
                            </tr>
                        </table>
                    ');
                    
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
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $htmlWithFooter . '</body></html>';
        exit;
    }
}