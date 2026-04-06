<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StockModel;
use App\Models\CompteModel;

class StockController extends Controller
{
    public function delete()
    {
        $this->requireRole(['accountant', 'admin', 'stock_manager']);
        // accepter GET id (lien) ou POST id (form)
        $id = $_GET['id'] ?? ($_POST['id'] ?? '');
        $token = $_GET['token'] ?? ($_POST['csrf_token'] ?? '');
        if (!\App\Core\Csrf::checkToken($token)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
        } elseif ($id) {
            $model = new StockModel();
            $model->deleteEntry($id);
        }
        header('Location: ?page=stock');
        exit;
    }

    public function edit()
    {
        $this->requireRole(['accountant', 'admin', 'stock_manager']);
        $model = new StockModel();
        $id = $_GET['id'] ?? ($_POST['id'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
                exit;
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
            
            // récupérer l'entrée actuelle pour connaître son ancienne opération
            $currentEntry = $model->getEntry($id);
            $oldOperation = $currentEntry['operation'] ?? '';
            $oldQte = floatval($currentEntry['quantite'] ?? 0);

            $stock_qte = $last ? ($last['stock']['qte'] ?? 0) : 0;
            
            // Recalculer le stock en annulant l'ancienne opération
            if ($oldOperation === 'entree') {
                $stock_qte -= $oldQte;
            } else if ($oldOperation === 'sortie') {
                $stock_qte += $oldQte;
            }
            
            // VÉRIFICATION: Pour une sortie, vérifier que le stock est suffisant
            if ($operation === 'sortie') {
                if ($stock_qte < $qte) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => sprintf(
                            'Erreur: Stock insuffisant pour "%s". Stock disponible: %s, quantité demandée: %s',
                            $compte,
                            number_format($stock_qte, 2, '.', ' '),
                            number_format($qte, 2, '.', ' ')
                        )
                    ]);
                    exit;
                }
                $stock_qte -= $qte;
            } else if ($operation === 'entree') {
                $stock_qte += $qte;
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
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Opération modifiée avec succès']);
            exit;
        }

        $entry = $model->getEntry($id);
        $stocks = $model->getAll();
        $this->render('stock_edit', ['entry' => $entry, 'stocks' => $stocks]);
    }

    public function add()
    {
        $this->requireRole(['accountant', 'admin', 'stock_manager']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
                exit;
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
            
            // VÉRIFICATION: Pour une sortie, vérifier que le stock est suffisant
            if ($operation === 'sortie') {
                if ($stock_qte < $qte) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => sprintf(
                            'Erreur: Stock insuffisant pour "%s". Stock disponible: %s, quantité demandée: %s',
                            $compte,
                            number_format($stock_qte, 2, '.', ' '),
                            number_format($qte, 2, '.', ' ')
                        )
                    ]);
                    exit;
                }
                $stock_qte -= $qte;
            } else if ($operation === 'entree') {
                $stock_qte += $qte;
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

            // Tenter d'ajouter automatiquement le compte au PLAN.xlsx (ne doit pas empêcher l'enregistrement)
            try {
                $cm = new CompteModel();
                $cm->addIfMissing($compte, $intitule);
            } catch (\Throwable $e) {
                error_log('StockController: ajout compte au PLAN.xlsx échoué: ' . $e->getMessage());
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Opération enregistrée avec succès']);
            exit;
        }

        $model = new StockModel();
        $stocks = $model->getAll();
        $this->render('stock', ['stocks' => $stocks]);
    }

    public function index()
    {
        $this->requireRole(['accountant', 'manager', 'admin', 'stock_manager']);
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

        // Déterminer le nombre de pages pour afficher la dernière par défaut
        $itemsPerPage = 20;

        // Construire la requête pour compter
        $query = [];
        if (!empty($dateDebut) || !empty($dateFin)) {
            $dateQuery = [];
            if (!empty($dateDebut)) {
                $d = \DateTime::createFromFormat('Y-m-d', $dateDebut);
                if ($d)
                    $dateQuery['$gte'] = $d->format('Y-m-d');
            }
            if (!empty($dateFin)) {
                $d2 = \DateTime::createFromFormat('Y-m-d', $dateFin);
                if ($d2)
                    $dateQuery['$lte'] = $d2->format('Y-m-d');
            }
            if (!empty($dateQuery))
                $query['date'] = $dateQuery;
        }
        if (!empty($compteFiltre)) {
            $query['compte'] = ['$regex' => $compteFiltre, '$options' => 'i'];
        }
        if (!empty($lieu)) {
            $query['lieu'] = ['$regex' => $lieu, '$options' => 'i'];
        }

        $totalCount = $model->countDocuments($query);
        $totalPages = ceil($totalCount / $itemsPerPage);

        // Si page_num n'est pas fourni, afficher la dernière page
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : max(1, $totalPages);

        $result = $model->getFilteredWithPagination($filters, $page, $itemsPerPage);
        $stocks = $result['items'];
        $pagination = $result['pagination'];
        $comptesMap = $model->getComptesMap();

        $this->render('stock', [
            'stocks' => $stocks,
            'comptesMap' => $comptesMap,
            'pagination' => $pagination
        ]);
    }

    public function export()
    {
        $this->requireRole(['accountant', 'manager', 'admin', 'stock_manager']);
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

        // Afficher les filtres actifs si présents
        $activeFilters = [];
        if (!empty($filters['date_debut']))
            $activeFilters[] = 'Depuis: ' . htmlspecialchars($filters['date_debut']);
        if (!empty($filters['date_fin']))
            $activeFilters[] = 'Jusqu\'au: ' . htmlspecialchars($filters['date_fin']);
        if (!empty($filters['compte_filtre']))
            $activeFilters[] = 'Compte: ' . htmlspecialchars($filters['compte_filtre']);
        if (!empty($filters['lieu']))
            $activeFilters[] = 'Lieu: ' . htmlspecialchars($filters['lieu']);

        if (!empty($activeFilters)) {
            $html .= '<div style="background:#f8f9fa;padding:8px;margin-bottom:12px;border:1px solid #dee2e6;border-radius:4px;">';
            $html .= '<strong>Filtres appliqués:</strong> ' . implode(' | ', $activeFilters);
            $html .= '</div>';
        }

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