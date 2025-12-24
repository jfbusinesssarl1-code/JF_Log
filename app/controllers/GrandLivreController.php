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
}
