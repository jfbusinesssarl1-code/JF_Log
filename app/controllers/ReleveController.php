<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\JournalModel;

class ReleveController extends Controller
{
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
        $totals = ['debit' => 0.0, 'credit' => 0.0];
        foreach ($entries as $e) {
            $totals['debit'] += isset($e['debit']) && is_numeric($e['debit']) ? floatval($e['debit']) : 0.0;
            $totals['credit'] += isset($e['credit']) && is_numeric($e['credit']) ? floatval($e['credit']) : 0.0;
        }
        $this->render('releve', [
            'entries' => $entries,
            'filters' => $filters,
            'totals' => $totals
        ]);
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

        $model = new JournalModel();
        $filters = [
            'compte' => $_GET['compte'] ?? '',
            'lieu' => $_GET['lieu'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $entries = $model->getFiltered($filters);

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Relevé des comptes');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Lieu</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
        $sumD = 0.0; $sumC = 0.0;
        foreach ($entries as $e) {
            $d = isset($e['debit']) && is_numeric($e['debit']) ? floatval($e['debit']) : 0.0;
            $c = isset($e['credit']) && is_numeric($e['credit']) ? floatval($e['credit']) : 0.0;
            $sumD += $d; $sumC += $c;
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['lieu'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['compte'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($d,2,'.','')) . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars(number_format($c,2,'.','')) . '</td>';
            $html .= '</tr>';
        }
        $html .= '<tr style="font-weight:700;background:#f1f3f5">';
        $html .= '<td colspan="4">Total</td>';
        $html .= '<td style="text-align:right">' . number_format($sumD,2,'.','') . '</td>';
        $html .= '<td style="text-align:right">' . number_format($sumC,2,'.','') . '</td>';
        $html .= '</tr>';
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: Releve export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('releve.pdf', \Mpdf\Output\Destination::DOWNLOAD);
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
        header('Content-Disposition: attachment; filename="releve.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}