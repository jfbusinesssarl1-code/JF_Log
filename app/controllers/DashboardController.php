<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\BalanceModel;
use App\Models\JournalModel;
class DashboardController extends Controller
{
    public function index()
    {
        $this->requireAuth();
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $username = $_SESSION['user']['username'] ?? 'Utilisateur';

        $balanceModel = new BalanceModel();
        $journalModel = new JournalModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        
        // Récupérer TOUTES les balances et journal (sans filtre) pour les graphiques côté client
        $balances = $balanceModel->getBalance([]);
        $journalFull = $journalModel->getFiltered([]);

        // pagination pour le tableau détaillé
        $itemsPerPage = 20;
        $query = [];
        if (!empty($filters['compte'])) {
            $query['compte'] = ['$regex' => $filters['compte'], '$options' => 'i'];
        }
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut']))
                $dateQuery['$gte'] = $filters['date_debut'];
            if (!empty($filters['date_fin']))
                $dateQuery['$lte'] = $filters['date_fin'];
            if (!empty($dateQuery))
                $query['date'] = $dateQuery;
        }
        $totalCount = $journalModel->countDocuments($query);
        $totalPages = $totalCount > 0 ? ceil($totalCount / $itemsPerPage) : 1;
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : max(1, $totalPages);

        $result = $journalModel->getFilteredWithPagination($filters, $page, $itemsPerPage);
        $journalPaged = $result['entries'];
        $pagination = $result['pagination'];

        $this->render('dashboard', [
            'balances' => $balances,
            'journal' => $journalFull,
            'journal_paged' => $journalPaged,
            'pagination' => $pagination,
            'username' => $username,
            'userRole' => $userRole,
            'filters' => $filters
        ]);
    }

    public function export()
    {
        $this->requireAuth();
        $format = $_GET['format'] ?? 'pdf';
        if ($format !== 'pdf')
            $format = 'pdf';
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            header('Content-Type: text/plain; charset=utf-8');
            echo "DEBUG export: class_exists Mpdf? " . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no') . "\n";
            echo "PHP SAPI: " . PHP_SAPI . "\n";
            echo "User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'n/a') . "\n";
            exit;
        }

        $balanceModel = new BalanceModel();
        $journalModel = new JournalModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
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
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut']))
                $dateQuery['$gte'] = $filters['date_debut'];
            if (!empty($filters['date_fin']))
                $dateQuery['$lte'] = $filters['date_fin'];
            if (!empty($dateQuery))
                $query['date'] = $dateQuery;
        }
        $totalCount = $journalModel->countDocuments($query);
        $journal = $journalModel->getFilteredLimited($filters, $maxItems);

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Export Journal Comptable');
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

        if (!empty($activeFilters)) {
            $html .= '<div style="background:#f8f9fa;padding:8px;margin-bottom:12px;border:1px solid #dee2e6;border-radius:4px;">';
            $html .= '<strong>Filtres appliqués:</strong> ' . implode(' | ', $activeFilters);
            $html .= '</div>';
        }

        if ($totalCount > $maxItems) {
            $html .= '<div style="background:#fff3cd;padding:8px;margin-bottom:12px;border:1px solid #ffeeba;border-radius:4px;">';
            $html .= '<strong>Attention :</strong> export limité à ' . $maxItems . ' lignes sur ' . $totalCount . '. ';
            $html .= 'Veuillez filtrer par période pour un export complet.';
            $html .= '</div>';
        }

        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Libellé</th><th>Quantité</th><th>Prix Unitaire</th><th>Prix Global</th></tr></thead><tbody>';
        foreach ($journal as $e) {
            $q = isset($e['quantite']) && is_numeric($e['quantite']) ? floatval($e['quantite']) : 0.0;
            $pu = isset($e['prix_unitaire']) && is_numeric($e['prix_unitaire']) ? floatval($e['prix_unitaire']) : 0.0;
            $pg = ($q > 0 && $pu > 0) ? ($q * $pu) : (isset($e['debit']) ? floatval($e['debit']) : 0.0);
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($q, 2, '.', '')) . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($pu, 2, '.', '')) . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($pg, 2, '.', '')) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $htmlWithFooter = $html . $footer;

        if ($format === 'pdf') {
            error_log('DEBUG: Dashboard export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->SetHTMLFooter($footer);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('dashboard_journal.pdf', \Mpdf\Output\Destination::DOWNLOAD);
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
        header('Content-Disposition: attachment; filename="dashboard_journal.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $htmlWithFooter . '</body></html>';
        exit;
    }
}