<?php
namespace App\Controllers;
use App\Core\Controller;

use App\Models\UserModel;
class LoginController extends Controller
{
    public function index()
    {
        // Démarrer la session au début
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                $error = 'Erreur CSRF';
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if (strlen($username) < 3 || strlen($username) > 32 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                    $error = "Nom d'utilisateur invalide";
                } elseif (strlen($password) < 6) {
                    $error = "Mot de passe trop court";
                } else {
                    $model = new UserModel();
                    $user = $model->verify($username, $password);
                    if ($user) {
                        $_SESSION['user'] = [
                            '_id' => (string) $user['_id'],
                            'username' => $user['username'],
                            'role' => $user['role'] ?? 'user'
                        ];
                        header('Location: ?page=dashboard');
                        exit;
                    } else {
                        $error = 'Identifiants invalides';
                    }
                }
            }
        }
        $this->render('login', ['error' => $error]);
    }
}
