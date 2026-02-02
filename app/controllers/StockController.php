<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StockModel;

class StockController extends Controller
{
    public function delete()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        // accepter GET id (lien) ou POST id (form)
        $id = $_GET['id'] ?? ($_POST['id'] ?? '');
        if ($id) {
            $model = new StockModel();
            $model->deleteEntry($id);
        }
        header('Location: ?page=stock');
        exit;
    }

    public function edit()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $model = new StockModel();
        $id = $_GET['id'] ?? ($_POST['id'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                die('Erreur CSRF');
            }
            $operation = $_POST['operation'] ?? '';
            $compte = $_POST['compte'] ?? '';
            $intitule = $_POST['intitule'] ?? '';
            $lieu = trim($_POST['lieu'] ?? '');
            $date = $_POST['date'] ?? '';
            $designation = $_POST['designation'] ?? '';
            $qte = floatval($_POST['quantite'] ?? 0);
            $pu = floatval($_POST['pu'] ?? 0);
            $total = $qte * $pu;

            // récupérer le dernier état pour ce compte (hors l'entrée modifiée)
            $last = $model->getLastByCompte($compte);

            $stock_qte = $last ? ($last['stock']['qte'] ?? 0) : 0;
            // recalcul simple : ici on applique l'opération sur la quantité trouvée (bonne pratique: recalculer tous les mouvements)
            if ($operation === 'entree') {
                $stock_qte += $qte;
            } else if ($operation === 'sortie') {
                $stock_qte -= $qte;
                if ($stock_qte < 0)
                    $stock_qte = 0;
            }

            $data = [
                'operation' => $operation,
                'compte' => $compte,
                'intitule' => $intitule,
                'lieu' => $lieu,
                'date' => $date,
                'designation' => $designation,
                'quantite' => $qte,
                'pu' => $pu,
                'pg' => $total,
                'entree' => [
                    'qte' => $operation === 'entree' ? $qte : 0,
                    'pu' => $operation === 'entree' ? $pu : 0,
                    'total' => $operation === 'entree' ? $total : 0
                ],
                'sortie' => [
                    'qte' => $operation === 'sortie' ? $qte : 0,
                    'pu' => $operation === 'sortie' ? $pu : 0,
                    'total' => $operation === 'sortie' ? $total : 0
                ],
                'stock' => [
                    'qte' => $stock_qte,
                    'pu' => $last ? ($last['stock']['pu'] ?? 0) : 0,
                    'total' => $last ? ($last['stock']['total'] ?? 0) : 0
                ]
            ];

            $model->updateEntry($id, $data);
            header('Location: ?page=stock');
            exit;
        }

        $entry = $model->getEntry($id);
        $stocks = $model->getAll();
        $this->render('stock_edit', ['entry' => $entry, 'stocks' => $stocks]);
    }

    public function add()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                die('Erreur CSRF');
            }
            $operation = $_POST['operation'] ?? '';
            $compte = $_POST['compte'] ?? '';
            $intitule = $_POST['intitule'] ?? '';
            $lieu = trim($_POST['lieu'] ?? '');
            $date = $_POST['date'] ?? '';
            $designation = $_POST['designation'] ?? '';
            $qte = floatval($_POST['quantite'] ?? 0);
            // si sortie, PU peut être absent
            $pu = isset($_POST['pu']) ? floatval($_POST['pu']) : 0;
            $total = $qte * $pu;

            $model = new StockModel();

            // récupérer le dernier stock pour ce compte (plus fiable via requête)
            $last = $model->getLastByCompte($compte);

            $stock_qte = $last ? ($last['stock']['qte'] ?? 0) : 0;
            if ($operation === 'entree') {
                $stock_qte += $qte;
            } else if ($operation === 'sortie') {
                // appliquer PEPS non reconstitué ici : on décrémente la qte
                $stock_qte -= $qte;
                if ($stock_qte < 0)
                    $stock_qte = 0;
            }

            $data = [
                'operation' => $operation,
                'compte' => $compte,
                'intitule' => $intitule,
                'lieu' => $lieu,
                'date' => $date,
                'designation' => $designation,
                'quantite' => $qte,
                'pu' => $pu,
                'pg' => $total,
                'entree' => [
                    'qte' => $operation === 'entree' ? $qte : 0,
                    'pu' => $operation === 'entree' ? $pu : 0,
                    'total' => $operation === 'entree' ? $total : 0
                ],
                'sortie' => [
                    'qte' => $operation === 'sortie' ? $qte : 0,
                    'pu' => $operation === 'sortie' ? $pu : 0,
                    'total' => $operation === 'sortie' ? $total : 0
                ],
                'stock' => [
                    'qte' => $stock_qte,
                    'pu' => $last ? ($last['stock']['pu'] ?? 0) : 0,
                    'total' => $last ? ($last['stock']['total'] ?? 0) : 0
                ]
            ];

            $model->addEntry($data);
            header('Location: ?page=stock');
            exit;
        }

        $model = new StockModel();
        $stocks = $model->getAll();
        $this->render('stock', ['stocks' => $stocks]);
    }

    public function index()
    {
        $this->requireRole(['accountant', 'manager', 'admin']);
        $model = new StockModel();

        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin = $_GET['date_fin'] ?? null;
        $compteFiltre = $_GET['compte_filtre'] ?? null;
        $lieu = $_GET['lieu'] ?? null;

        $filters = [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'compte_filtre' => $compteFiltre,
            'lieu' => $lieu
        ];

        $stocks = $model->getFiltered($filters);
        $comptesMap = $model->getComptesMap();

        $this->render('stock', ['stocks' => $stocks, 'comptesMap' => $comptesMap]);
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

        $model = new StockModel();
        $filters = [
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null,
            'compte_filtre' => $_GET['compte_filtre'] ?? null,
            'lieu' => $_GET['lieu'] ?? null
        ];
        $stocks = $model->getFiltered($filters);

        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $header = \App\Helpers\PdfHelper::renderHeader('Stock');

        $html = $header;
        $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Compte</th><th>Lieu</th><th>Intitulé</th><th>Désignation</th><th>Entrée Qte</th><th>Entrée PU</th><th>Entrée Total</th><th>Sortie Qte</th><th>Stock Qte</th></tr></thead><tbody>';
        foreach ($stocks as $s) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($s['date'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($s['compte'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($s['lieu'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($s['intitule'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($s['designation'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($s['entree']['qte'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($s['entree']['pu'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($s['entree']['total'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($s['sortie']['qte'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . htmlspecialchars($s['stock']['qte'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if ($format === 'pdf') {
            error_log('DEBUG: Stock export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output('stock.pdf', \Mpdf\Output\Destination::DOWNLOAD);
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
        header('Content-Disposition: attachment; filename="stock.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }
}