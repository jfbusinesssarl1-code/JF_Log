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
}
