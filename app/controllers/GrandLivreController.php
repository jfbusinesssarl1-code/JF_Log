<?php
namespace App\Controllers;
use App\Core\Controller;

use App\Models\GrandLivreModel;
class GrandLivreController extends Controller
{
    public function delete()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new GrandLivreModel();
            $model->delete($id);
        }
        header('Location: ?page=grandlivre&compte=' . urlencode($_GET['compte'] ?? ''));
        exit;
    }

    public function edit()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $id = $_GET['id'] ?? null;
        $model = new GrandLivreModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $data = [
                'date' => $_POST['date'] ?? '',
                'libelle' => $_POST['libelle'] ?? '',
                'debit' => floatval($_POST['debit'] ?? 0),
                'credit' => floatval($_POST['credit'] ?? 0)
            ];
            $model->update($id, $data);
            header('Location: ?page=grandlivre&compte=' . urlencode($_GET['compte'] ?? ''));
            exit;
        }
        $entry = null;
        if ($id) {
            $entry = $model->getById($id);
        }
        $comptes = $model->getComptes();
        $selected = $_GET['compte'] ?? ($comptes[0] ?? null);
        $entries = $selected ? $model->getByCompte($selected) : [];
        $this->render('grandlivre_edit', [
            'entry' => $entry,
            'comptes' => $comptes,
            'selected' => $selected,
            'entries' => $entries
        ]);
    }
    public function index()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $model = new GrandLivreModel();
        $comptes = $model->getComptes();
        $selected = $_GET['compte'] ?? ($comptes[0] ?? null);
        $filters = [
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $entries = $selected ? $model->getByCompte($selected, $filters) : [];
        $this->render('grandlivre', [
            'comptes' => $comptes,
            'selected' => $selected,
            'entries' => $entries
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

        $model = new GrandLivreModel();
        $selected = $_GET['compte'] ?? null;
        $filters = [
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
        $entries = $selected ? $model->getByCompte($selected, $filters) : [];

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Grand Livre');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
        foreach ($entries as $e) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($e['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($e['libelle'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['debit'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($e['credit'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: GrandLivre export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('grandlivre.pdf', \Mpdf\Output\Destination::DOWNLOAD);
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
        header('Content-Disposition: attachment; filename="grandlivre.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}
