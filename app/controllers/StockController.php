<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StockModel;

class StockController extends Controller
{
    public function delete()
    {
        $this->requireAuth();
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
        $this->requireAuth();
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
        $this->requireRole(['manager', 'admin']);
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
        $this->requireRole(['manager', 'admin']);
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
}