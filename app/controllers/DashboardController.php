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
        $balances = $balanceModel->getBalance($filters);
        $journal = $journalModel->getFiltered($filters);
        $this->render('dashboard', [
            'balances' => $balances,
            'journal' => $journal,
            'username' => $username,
            'userRole' => $userRole
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
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $journal = $journalModel->getFiltered($filters);

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Export Journal Comptable');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
        foreach ($journal as $e) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['compte'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['debit'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['credit'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: Dashboard export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
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
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}
