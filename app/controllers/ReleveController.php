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
        $this->render('releve', [
            'entries' => $entries,
            'filters' => $filters
        ]);
    }
}