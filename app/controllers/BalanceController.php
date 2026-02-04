<?php
namespace App\Controllers;
use App\Core\Controller;

use App\Models\BalanceModel;
class BalanceController extends Controller
{
    public function index()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $model = new BalanceModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $balances = $model->getBalance($filters);
        $totals = ['debit' => 0.0, 'credit' => 0.0, 'solde' => 0.0];
        foreach ($balances as $b) {
            $d = isset($b['debit']) && is_numeric($b['debit']) ? floatval($b['debit']) : 0.0;
            $c = isset($b['credit']) && is_numeric($b['credit']) ? floatval($b['credit']) : 0.0;
            $totals['debit'] += $d;
            $totals['credit'] += $c;
            $totals['solde'] += ($d - $c);
        }
        $this->render('balance', ['balances' => $balances, 'totals' => $totals]);
    }

    public function export()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
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

        $model = new \App\Models\BalanceModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $balances = $model->getBalance($filters);

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Balance');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Compte</th><th>Débit</th><th>Crédit</th><th>Solde</th></tr></thead><tbody>';
        $sumD = 0.0; $sumC = 0.0;
        foreach ($balances as $b) {
            $d = isset($b['debit']) && is_numeric($b['debit']) ? floatval($b['debit']) : 0.0;
            $c = isset($b['credit']) && is_numeric($b['credit']) ? floatval($b['credit']) : 0.0;
            $sumD += $d; $sumC += $c;
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($b['_id'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($d,2,'.','')) . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($c,2,'.','')) . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format(($d - $c),2,'.','')) . '</td>';
            $html .= '</tr>';
        }
        $html .= '<tr style="font-weight:700;background:#f1f3f5">';
        $html .= '<td>Total</td>';
        $html .= '<td style="text-align:right">' . number_format($sumD,2,'.','') . '</td>';
        $html .= '<td style="text-align:right">' . number_format($sumC,2,'.','') . '</td>';
        $html .= '<td style="text-align:right">' . number_format(($sumD - $sumC),2,'.','') . '</td>';
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: Balance export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('balance.pdf', \Mpdf\Output\Destination::DOWNLOAD);
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
        header('Content-Disposition: attachment; filename="balance.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}
