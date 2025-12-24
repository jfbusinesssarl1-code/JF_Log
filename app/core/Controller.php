<?php
namespace App\Core;

class Controller
{
    protected function render($view, $data = [])
    {
        extract($data);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    protected function requireAuth()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }
    }

    /**
     * Vérifie que l'utilisateur connecté a l'un des rôles requis
     * @param array $allowedRoles - Tableau des rôles autorisés (ex: ['admin', 'manager'])
     */
    protected function requireRole($allowedRoles = [])
    {
        session_start();

        // Vérifier l'authentification d'abord
        if (empty($_SESSION['user'])) {
            header('Location: ?page=login');
            exit;
        }

        // Si pas de rôles spécifiés, tous les utilisateurs authentifiés peuvent accéder
        if (empty($allowedRoles)) {
            return;
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = $_SESSION['user']['role'] ?? 'user';

        if (!in_array($userRole, $allowedRoles)) {
            // Accès refusé - rediriger vers le dashboard
            header('Location: ?page=dashboard');
            exit;
        }
    }

    /**
     * Vérifie que l'utilisateur est administrateur
     */
    protected function requireAdmin()
    {
        $this->requireRole(['admin']);
    }
}
