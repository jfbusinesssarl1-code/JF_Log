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
        $this->render('balance', ['balances' => $balances]);
    }
}
